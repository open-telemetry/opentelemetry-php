<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Instrumentation\General;

use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\GeneralInstrumentationConfiguration;
use OpenTelemetry\API\Instrumentation\Configuration\General\PeerConfig;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * @implements ComponentProvider<GeneralInstrumentationConfiguration>
 */
class PeerConfigProvider implements ComponentProvider
{
    public function createPlugin(array $properties, Context $context): GeneralInstrumentationConfiguration
    {
        return new PeerConfig($properties);
    }

    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('peer');
        $node
            ->children()
                ->arrayNode('service_mapping')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('peer')->end()
                            ->scalarNode('service')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }
}
