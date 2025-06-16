<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Instrumentation\General;

use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\GeneralInstrumentationConfiguration;
use OpenTelemetry\API\Instrumentation\Configuration\General\HttpConfig;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * @implements ComponentProvider<GeneralInstrumentationConfiguration>
 */
class HttpConfigProvider implements ComponentProvider
{
    public function createPlugin(array $properties, Context $context): GeneralInstrumentationConfiguration
    {
        return new HttpConfig($properties);
    }

    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('http');
        $node
            ->children()
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
