<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Metrics;

use OpenTelemetry\Config\SDK\Configuration\ComponentPlugin;
use OpenTelemetry\Config\SDK\Configuration\ComponentProvider;
use OpenTelemetry\Config\SDK\Configuration\ComponentProviderRegistry;
use OpenTelemetry\Config\SDK\Configuration\Context;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;
use OpenTelemetry\SDK\Metrics\MetricReader\NoopReader;
use OpenTelemetry\SDK\Metrics\MetricReaderInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * @implements ComponentProvider<MetricReaderInterface>
 */
final class MetricReaderPull implements ComponentProvider
{

    /**
     * @param array{
     *     exporter: ComponentPlugin<MetricExporterInterface>,
     *     producers: array,
     * } $properties
     */
    public function createPlugin(array $properties, Context $context): MetricReaderInterface
    {
        return new NoopReader();
    }

    public function getConfig(ComponentProviderRegistry $registry): ArrayNodeDefinition
    {
        $node = new ArrayNodeDefinition('pull');
        $node
            ->children()
                ->append($registry->component('exporter', MetricExporterInterface::class)->isRequired())
                ->arrayNode('producers') //@todo
                    ->variablePrototype()->end()
            ->end()
        ;

        return $node;
    }
}
