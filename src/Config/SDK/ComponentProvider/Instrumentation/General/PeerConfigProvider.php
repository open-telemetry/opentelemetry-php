<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Instrumentation\General;

use OpenTelemetry\API\Instrumentation\AutoInstrumentation\GeneralInstrumentationConfiguration;
use OpenTelemetry\API\Instrumentation\Configuration\General\PeerConfig;
use OpenTelemetry\Config\SDK\Configuration\ComponentProvider;
use OpenTelemetry\Config\SDK\Configuration\ComponentProviderRegistry;
use OpenTelemetry\Config\SDK\Configuration\Context;
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
