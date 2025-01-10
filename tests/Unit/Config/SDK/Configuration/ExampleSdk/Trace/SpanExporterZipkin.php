<?php

declare(strict_types=1);

namespace ExampleSDK\ComponentProvider\Trace;

use BadMethodCallException;
use ExampleSDK\Trace\SpanExporter;
use OpenTelemetry\Config\SDK\Configuration\ComponentProvider;
use OpenTelemetry\Config\SDK\Configuration\ComponentProviderRegistry;
use OpenTelemetry\Config\SDK\Configuration\Context;
use OpenTelemetry\Config\SDK\Configuration\Validation;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

final class SpanExporterZipkin implements ComponentProvider
{
    /**
     * @param array{
     *     endpoint: string,
     *     timeout: int<0, max>,
     * } $properties
     */
    public function createPlugin(array $properties, Context $context): SpanExporter
    {
        throw new BadMethodCallException('not implemented');
    }

    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('zipkin');
        $node
            ->children()
                ->scalarNode('endpoint')->isRequired()->validate()->always(Validation::ensureString())->end()->end()
                ->integerNode('timeout')->min(0)->defaultValue(10)->end()
            ->end()
        ;

        return $node;
    }
}
