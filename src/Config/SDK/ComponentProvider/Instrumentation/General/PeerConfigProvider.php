<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Instrumentation\General;

use OpenTelemetry\API\Instrumentation\AutoInstrumentation\GeneralInstrumentationConfiguration;
use OpenTelemetry\Config\SDK\Configuration\ComponentProvider;
use OpenTelemetry\Config\SDK\Configuration\ComponentProviderRegistry;
use OpenTelemetry\Config\SDK\Configuration\Context;
use OpenTelemetry\SDK\Instrumentation\Configuration\PeerConfig;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * @implements ComponentProvider<GeneralInstrumentationConfiguration>
 */
class PeerConfigProvider implements ComponentProvider
{
    public function createPlugin(array $properties, Context $context): GeneralInstrumentationConfiguration
    {
        return new PeerConfig($properties);
    }

    public function getConfig(ComponentProviderRegistry $registry): ArrayNodeDefinition
    {
        $node = new ArrayNodeDefinition('peer');
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
