<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Internal;

use function array_key_first;
use function array_keys;
use function array_map;
use function count;
use function implode;
use InvalidArgumentException;
use LogicException;
use OpenTelemetry\Config\SDK\Configuration\ComponentProvider;
use OpenTelemetry\Config\SDK\Configuration\ResourceCollection;
use OpenTelemetry\Config\SDK\Configuration\Validation;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;
use function sprintf;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\NodeInterface;
use Symfony\Component\Config\Definition\Processor;

/**
 * @internal
 */
final class ComponentProviderRegistry implements \OpenTelemetry\Config\SDK\Configuration\ComponentProviderRegistry, ResourceTrackable
{
    /** @var iterable iterable<Normalization> */
    private readonly iterable $normalizations;
    private readonly NodeBuilder $builder;
    /** @var array<string, array<string, ComponentProviderRegistryEntry>> */
    private array $providers = [];
    private ?ResourceCollection $resources = null;

    /**
     * @param iterable<Normalization> $normalizations
     */
    public function __construct(iterable $normalizations, NodeBuilder $builder)
    {
        $this->normalizations = $normalizations;
        $this->builder = $builder;
    }

    public function register(ComponentProvider $provider): void
    {
        $config = $provider->getConfig($this, $this->builder);

        $name = self::loadName($config);
        $type = self::loadType($provider);
        if (isset($this->providers[$type][$name])) {
            throw new LogicException(sprintf('Duplicate component provider registered for "%s" "%s"', $type, $name));
        }

        $this->providers[$type][$name] = new ComponentProviderRegistryEntry($provider, $config);
    }

    public function trackResources(?ResourceCollection $resources): void
    {
        $this->resources = $resources;
    }

    public function component(string $name, string $type): NodeDefinition
    {
        $node = $this->builder->arrayNode($name);
        //$node->defaultNull();
        $this->applyToArrayNode($node, $type);

        return $node;
    }

    public function componentList(string $name, string $type): ArrayNodeDefinition
    {
        $node = $this->builder->arrayNode($name);
        $this->applyToArrayNode($node->arrayPrototype(), $type);

        return $node;
    }

    public function componentArrayList(string $name, string $type): ArrayNodeDefinition
    {
        $node = $this->builder->arrayNode($name);
        $this->applyToArrayNode($node->arrayPrototype(), $type);

        return $node;
    }

    public function componentNames(string $name, string $type): ArrayNodeDefinition
    {
        $node = $this->builder->arrayNode($name);
        $node->scalarPrototype()->validate()->always(Validation::ensureString())->end()->end();
        $node->validate()->always(function (array $value) use ($type): array {
            $plugins = [];
            foreach ($value as $name) {
                $plugins[] = $this->process($type, $name, []);
            }

            return $plugins;
        });

        return $node;
    }

    private function applyToArrayNode(ArrayNodeDefinition $node, string $type, bool $forceArray = false): void
    {
        $node->info(sprintf('Component "%s"', $type));
        $node->performNoDeepMerging();
        $node->ignoreExtraKeys(false);
        $node->validate()->always(function (array $value) use ($type): ComponentPlugin {
            if (count($value) !== 1) {
                throw new InvalidArgumentException(sprintf(
                    'Component "%s" must have exactly one provider defined, got %s',
                    $type,
                    implode(', ', array_map(json_encode(...), array_keys($value)) ?: ['none'])
                ));
            }

            return $this->process($type, array_key_first($value), $value);
        });
    }

    private function process(string $type, string $name, mixed $configs): ComponentPlugin
    {
        if (!$provider = $this->providers[$type][$name] ?? null) {
            throw new InvalidArgumentException(sprintf(
                'Component "%s" uses unknown provider "%s", available providers are %s',
                $type,
                $name,
                implode(', ', array_map(json_encode(...), array_keys($this->providers[$type] ?? [])) ?: ['none'])
            ));
        }

        if (!$provider->node instanceof NodeInterface) {
            foreach ($this->normalizations as $normalization) {
                $normalization->apply($provider->node);
            }
            $provider->node = $provider->node->getNode(forceRootNode: true);
        }

        try {
            $componentConfig = (new Processor())->process($provider->node, $configs);
        } catch (InvalidConfigurationException $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }

        $this->resources?->addClassResource($provider);

        return new ComponentPlugin($componentConfig, $provider->componentProvider);
    }

    /**
     * @psalm-suppress PossiblyNullFunctionCall,InaccessibleProperty
     */
    private static function loadName(NodeDefinition $node): string
    {
        static $accessor;
        // @phpstan-ignore-next-line
        $accessor ??= (static fn (NodeDefinition $node): ?string => $node->name)->bindTo(null, NodeDefinition::class);

        return $accessor($node);
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
