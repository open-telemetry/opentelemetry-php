<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Trace;

use OpenTelemetry\Config\SDK\Configuration\ComponentPlugin;
use OpenTelemetry\Config\SDK\Configuration\ComponentProvider;
use OpenTelemetry\Config\SDK\Configuration\ComponentProviderRegistry;
use OpenTelemetry\Config\SDK\Configuration\Context;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * @implements ComponentProvider<SpanProcessorInterface>
 */
final class SpanProcessorSimple implements ComponentProvider
{
    /**
     * @param array{
     *     exporter: ComponentPlugin<SpanExporterInterface>,
     * } $properties
     */
    public function createPlugin(array $properties, Context $context): SpanProcessorInterface
    {
        return new SimpleSpanProcessor(
            exporter: $properties['exporter']->create($context),
        );
    }

    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('simple');
        $node
            ->children()
                ->append($registry->component('exporter', SpanExporterInterface::class)->isRequired())
            ->end()
        ;

        return $node;
    }
}
