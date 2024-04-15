<?php

declare(strict_types=1);

namespace ExampleSDK\ComponentProvider\Metrics;

use BadMethodCallException;
use ExampleSDK\Metrics\MetricExporter;
use ExampleSDK\Metrics\MetricReader;
use OpenTelemetry\Config\SDK\Configuration\ComponentPlugin;
use OpenTelemetry\Config\SDK\Configuration\ComponentProvider;
use OpenTelemetry\Config\SDK\Configuration\ComponentProviderRegistry;
use OpenTelemetry\Config\SDK\Configuration\Context;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

final class MetricReaderPeriodic implements ComponentProvider
{

    /**
     * @param array{
     *     interval: int<0, max>,
     *     timeout: int<0, max>,
     *     exporter: ComponentPlugin<MetricExporter>,
     * } $properties
     */
    public function createPlugin(array $properties, Context $context): MetricReader
    {
        throw new BadMethodCallException('not implemented');
    }

    public function getConfig(ComponentProviderRegistry $registry): ArrayNodeDefinition
    {
        $node = new ArrayNodeDefinition('periodic');
        $node
            ->children()
                ->integerNode('interval')->min(0)->defaultValue(5000)->end()
                ->integerNode('timeout')->min(0)->defaultValue(30000)->end()
                ->append($registry->component('exporter', MetricExporter::class)->isRequired())
            ->end()
        ;

        return $node;
    }
}
