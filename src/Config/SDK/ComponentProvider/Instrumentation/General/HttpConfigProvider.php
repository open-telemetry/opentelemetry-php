<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Instrumentation\General;

use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\GeneralInstrumentationConfiguration;
use OpenTelemetry\API\Instrumentation\Configuration\General\HttpConfig;
use OpenTelemetry\Config\SDK\Configuration\Validation;
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
                    ->scalarPrototype()->validate()->always(Validation::ensureString())->end()->end()
                ->end()
                ->arrayNode('response_captured_headers')
                    ->requiresAtLeastOneElement()
                    ->scalarPrototype()->validate()->always(Validation::ensureString())->end()->end()
                ->end()
                ->arrayNode('known_methods')
                    ->scalarPrototype()->validate()->always(Validation::ensureString())->end()->end()
                ->end()
            ->end()
        ;

        return $node;
    }
}
