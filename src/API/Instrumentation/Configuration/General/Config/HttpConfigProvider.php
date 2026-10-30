<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Instrumentation\Configuration\General\Config;

use Closure;
use InvalidArgumentException;
use function is_string;
use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\GeneralInstrumentationConfiguration;
use OpenTelemetry\API\Instrumentation\Configuration\General\HttpConfig;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * @internal
 * @implements ComponentProvider<GeneralInstrumentationConfiguration>
 */
class HttpConfigProvider implements ComponentProvider
{
    #[\Override]
    public function createPlugin(array $properties, Context $context): GeneralInstrumentationConfiguration
    {
        return new HttpConfig($properties);
    }

    #[\Override]
    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('http');
        $node
            ->ignoreExtraKeys()
            ->children()
                ->append(SemconvConfig::node($builder))
                ->append($this->capturedHeaders('client', $builder))
                ->append($this->capturedHeaders('server', $builder))
            ->end()
        ;

        return $node;
    }

    private function capturedHeaders(string $name, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode($name);
        $node
            ->ignoreExtraKeys()
            ->children()
                ->arrayNode('request_captured_headers')
                    ->requiresAtLeastOneElement()
                    ->scalarPrototype()->validate()->always(self::ensureString())->end()->end()
                ->end()
                ->arrayNode('response_captured_headers')
                    ->requiresAtLeastOneElement()
                    ->scalarPrototype()->validate()->always(self::ensureString())->end()->end()
                ->end()
                ->arrayNode('known_methods')
                    ->scalarPrototype()->validate()->always(self::ensureString())->end()->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    /**
     * A copy of `OpenTelemetry\Config\SDK\Configuration\Validation::ensureString()`, which this
     * package cannot depend on.
     */
    private static function ensureString(): Closure
    {
        return static function (mixed $value): ?string {
            if ($value === null) {
                return null;
            }
            if (!is_string($value)) {
                throw new InvalidArgumentException('must be of type string');
            }

            return $value;
        };
    }
}
