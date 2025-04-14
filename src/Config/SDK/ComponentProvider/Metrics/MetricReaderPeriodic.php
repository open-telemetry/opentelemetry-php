<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Metrics;

use OpenTelemetry\Config\SDK\Configuration\ComponentPlugin;
use OpenTelemetry\Config\SDK\Configuration\ComponentProvider;
use OpenTelemetry\Config\SDK\Configuration\ComponentProviderRegistry;
use OpenTelemetry\Config\SDK\Configuration\Context;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;
use OpenTelemetry\SDK\Metrics\MetricReader\ExportingReader;
use OpenTelemetry\SDK\Metrics\MetricReaderInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * @implements ComponentProvider<MetricReaderInterface>
 */
final class MetricReaderPeriodic implements ComponentProvider
{
    /**
     * @param array{
     *     interval: int<0, max>,
     *     timeout: int<0, max>,
     *     exporter: ComponentPlugin<MetricExporterInterface>,
     *     producers: array,
     *     cardinality_limits: array{
     *         default: int<0, max>,
     *         counter: int<0, max>,
     *         gauge: int<0, max>,
     *         histogram: int<0, max>,
     *         observable_counter: int<0, max>,
     *         observable_gauge: int<0, max>,
     *         observable_up_down_counter: int<0, max>,
     *         up_down_counter: int<0, max>,
     *     },
     * } $properties
     */
    public function createPlugin(array $properties, Context $context): MetricReaderInterface
    {
        return new ExportingReader(
            exporter: $properties['exporter']->create($context),
        );
    }

    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('periodic');
        $node
            ->children()
                ->integerNode('interval')->min(0)->defaultValue(5000)->end()
                ->integerNode('timeout')->min(0)->defaultValue(30000)->end()
                ->append($registry->component('exporter', MetricExporterInterface::class)->isRequired())
                ->arrayNode('producers') //TODO not implemented in metrics API
                    ->variablePrototype()->end()
                ->end()
                ->arrayNode('cardinality_limits') //TODO not implemented in metrics API
                    ->variablePrototype()->end()
                ->end()
            ->end()
        ;

        return $node;
    }
}
