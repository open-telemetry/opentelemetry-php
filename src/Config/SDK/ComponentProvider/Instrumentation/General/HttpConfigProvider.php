<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Instrumentation\General;

use OpenTelemetry\API\Instrumentation\AutoInstrumentation\GeneralInstrumentationConfiguration;
use OpenTelemetry\Config\SDK\Configuration\ComponentProvider;
use OpenTelemetry\Config\SDK\Configuration\ComponentProviderRegistry;
use OpenTelemetry\Config\SDK\Configuration\Context;
use OpenTelemetry\SDK\Instrumentation\Configuration\HttpConfig;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * @implements ComponentProvider<GeneralInstrumentationConfiguration>
 */
class HttpConfigProvider implements ComponentProvider
{

    public function createPlugin(array $properties, Context $context): GeneralInstrumentationConfiguration
    {
        return new HttpConfig($properties);
    }

    public function getConfig(ComponentProviderRegistry $registry): ArrayNodeDefinition
    {
        $node = new ArrayNodeDefinition('http');
        $node
            ->children()
                ->append($this->capturedHeaders('client'))
                ->append($this->capturedHeaders('server'))
            ->end()
        ;

        return $node;
    }

    private function capturedHeaders(string $name): ArrayNodeDefinition
    {
        $node = new ArrayNodeDefinition($name);
        $node
            ->children()
                ->arrayNode('request_captured_headers')
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode('response_captured_headers')
                    ->scalarPrototype()->end()
                ->end()
            ->end()
        ;

        return $node;
    }
}
