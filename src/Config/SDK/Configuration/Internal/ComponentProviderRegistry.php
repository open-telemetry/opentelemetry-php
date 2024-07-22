<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Internal;

use function array_diff_key;
use function array_key_first;
use function array_keys;
use function array_map;
use function count;
use function implode;
use InvalidArgumentException;
use LogicException;
use OpenTelemetry\Config\SDK\Configuration\ComponentProvider;
use OpenTelemetry\Config\SDK\Configuration\ResourceCollection;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;
use function sprintf;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\VariableNodeDefinition;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * @internal
 */
final class ComponentProviderRegistry implements \OpenTelemetry\Config\SDK\Configuration\ComponentProviderRegistry, ResourceTrackable
{

    /** @var array<string, array<string, ComponentProvider>> */
    private array $providers = [];
    /** @var array<string, array<string, true>> */
    private array $recursionProtection = [];
    private ?ResourceCollection $resources = null;

    public function register(ComponentProvider $provider): void
    {
        $name = self::loadName($provider);
        $type = self::loadType($provider);
        if (isset($this->providers[$type][$name])) {
            throw new LogicException(sprintf('Duplicate component provider registered for "%s" "%s"', $type, $name));
        }

        $this->providers[$type][$name] = $provider;
    }

    public function trackResources(?ResourceCollection $resources): void
    {
        $this->resources = $resources;
    }

    public function component(string $name, string $type): NodeDefinition
    {
        if (!$this->getProviders($type)) {
            return (new VariableNodeDefinition($name))
                ->info(sprintf('Component "%s"', $type))
                ->defaultNull()
                ->validate()->always()->thenInvalid(sprintf('Component "%s" cannot be configured, it does not have any associated provider', $type))->end();
        }

        $node = new ArrayNodeDefaultNullDefinition($name);
        $this->applyToArrayNode($node, $type);

        return $node;
    }

    public function componentList(string $name, string $type): ArrayNodeDefinition
    {
        $node = new ArrayNodeDefinition($name);
        $this->applyToArrayNode($node, $type, true);

        return $node;
    }

    public function componentArrayList(string $name, string $type): ArrayNodeDefinition
    {
        $node = new ArrayNodeDefinition($name);
        $this->applyToArrayNode($node->arrayPrototype(), $type);

        return $node;
    }

    public function componentNames(string $name, string $type): ArrayNodeDefinition
    {
        $node = new ArrayNodeDefinition($name);

        $providers = $this->getProviders($type);
        foreach ($providers as $providerName => $provider) {
            try {
                $provider->getConfig(new ComponentProviderRegistry())->getNode(true)->finalize([]);
            } catch (InvalidConfigurationException) {
                unset($providers[$providerName]);
            }
        }
        if ($providers) {
            $node->enumPrototype()->values(array_keys($providers))->end();

            $node->validate()->always(function (array $value) use ($type): array {
                $plugins = [];
                foreach ($value as $name) {
                    $provider = $this->providers[$type][$name];
                    $this->resources?->addClassResource($provider);

                    $plugins[] = new ComponentPlugin([], $provider);
                }

                return $plugins;
            });
        }

        return $node;
    }

    private function applyToArrayNode(ArrayNodeDefinition $node, string $type, bool $forceArray = false): void
    {
        $node->info(sprintf('Component "%s"', $type));
        $node->performNoDeepMerging();

        foreach ($this->getProviders($type) as $name => $provider) {
            $this->recursionProtection[$type][$name] = true;

            try {
                $node->children()->append($provider->getConfig($this));
            } finally {
                unset($this->recursionProtection[$type][$name]);
            }
        }

        if ($forceArray) {
            // if the config was a map rather than an array, force it back to an array
            $node->validate()->always(function (array $value) use ($type): array {
                $validated = [];
                foreach ($value as $name => $v) {
                    $provider = $this->providers[$type][$name];
                    $this->resources?->addClassResource($provider);
                    $validated[] = new ComponentPlugin($v, $this->providers[$type][$name]);
                }

                return $validated;
            });
        } else {
            $node->validate()->always(function (array $value) use ($type): ComponentPlugin {
                if (count($value) !== 1) {
                    throw new InvalidArgumentException(sprintf(
                        'Component "%s" must have exactly one element defined, got %s',
                        $type,
                        implode(', ', array_map(json_encode(...), array_keys($value)) ?: ['none'])
                    ));
                }

                $name = array_key_first($value);
                $provider = $this->providers[$type][$name];
                $this->resources?->addClassResource($provider);

                return new ComponentPlugin($value[$name], $this->providers[$type][$name]);
            });
        }
    }

    /**
     * Returns all registered providers for a specific component type.
     *
     * @param string $type the component type to load providers for
     * @return array<string, ComponentProvider> component providers indexed by their name
     */
    private function getProviders(string $type): array
    {
        return array_diff_key(
            $this->providers[$type] ?? [],
            $this->recursionProtection[$type] ?? [],
        );
    }

    /**
     * @psalm-suppress PossiblyNullFunctionCall,InaccessibleProperty
     */
    private static function loadName(ComponentProvider $provider): string
    {
        static $accessor; //@todo inaccessible property $node->name
        /** @phpstan-ignore-next-line */
        $accessor ??= (static fn (NodeDefinition $node): ?string => $node->name)->bindTo(null, NodeDefinition::class);

        return $accessor($provider->getConfig(new ComponentProviderRegistry()));
    }

    private static function loadType(ComponentProvider $provider): string
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        if ($returnType = (new ReflectionClass($provider))->getMethod('createPlugin')->getReturnType()) {
            return self::typeToString($returnType);
        }

        return 'mixed';
    }

    private static function typeToString(ReflectionType $type): string
    {
        /** @phpstan-ignore-next-line */
        return match ($type::class) {
            ReflectionNamedType::class => $type->getName(),
            ReflectionUnionType::class => implode('|', array_map(self::typeToString(...), $type->getTypes())),
            ReflectionIntersectionType::class => implode('&', array_map(self::typeToString(...), $type->getTypes())),
        };
    }
}
