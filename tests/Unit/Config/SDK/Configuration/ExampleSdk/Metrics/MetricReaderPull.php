<?php

declare(strict_types=1);

namespace ExampleSDK\ComponentProvider\Metrics;

use BadMethodCallException;
use ExampleSDK\Metrics\MetricExporter;
use ExampleSDK\Metrics\MetricReader;
use OpenTelemetry\API\Configuration\Config\ComponentPlugin;
use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

final class MetricReaderPull implements ComponentProvider
{
    /**
     * @param array{
     *     exporter: ComponentPlugin<MetricExporter>,
     * } $properties
     */
    #[\Override]
    public function createPlugin(array $properties, Context $context): MetricReader
    {
        throw new BadMethodCallException('not implemented');
    }

    #[\Override]
    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('pull');
        $node
            ->children()
                ->append($registry->component('exporter', MetricExporter::class)->isRequired())
            ->end()
        ;

        return $node;
    }
}
