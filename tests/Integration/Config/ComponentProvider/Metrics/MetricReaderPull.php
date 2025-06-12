<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Integration\Config\ComponentProvider\Metrics;

use OpenTelemetry\Config\SDK\Configuration\ComponentPlugin;
use OpenTelemetry\Config\SDK\Configuration\ComponentProvider;
use OpenTelemetry\Config\SDK\Configuration\ComponentProviderRegistry;
use OpenTelemetry\Config\SDK\Configuration\Context;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;
use OpenTelemetry\SDK\Metrics\MetricReaderInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * @implements ComponentProvider<MetricReaderInterface>
 */
final class MetricReaderPull implements ComponentProvider
{
    /**
     * @param array{
     *     exporter: ComponentPlugin<MetricExporterInterface>,
     *     producers: array,
     *     cardinality_limits: array,
     * } $properties
     */
    public function createPlugin(array $properties, Context $context): MetricReaderInterface
    {
        return new class() implements MetricReaderInterface {
            public function collect(): bool
            {
                return true;
            }

            public function shutdown(): bool
            {
                return true;
            }

            public function forceFlush(): bool
            {
                return true;
            }
        };
    }

    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('pull');
        $node
            ->children()
                ->append($registry->component('exporter', MetricExporterInterface::class)->isRequired())
                ->arrayNode('producers') //@todo
                    ->variablePrototype()->end()
                ->end()
                ->arrayNode('cardinality_limits') //@todo
                    ->variablePrototype()->end()
                ->end()
            ->end()
        ;

        return $node;
    }
}
