<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Trace;

use OpenTelemetry\API\Common\Time\Clock;
use OpenTelemetry\Config\SDK\Configuration\ComponentPlugin;
use OpenTelemetry\Config\SDK\Configuration\ComponentProvider;
use OpenTelemetry\Config\SDK\Configuration\ComponentProviderRegistry;
use OpenTelemetry\Config\SDK\Configuration\Context;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * @implements ComponentProvider<SpanProcessorInterface>
 */
final class SpanProcessorBatch implements ComponentProvider
{

    /**
     * @param array{
     *     schedule_delay: int<0, max>,
     *     export_timeout: int<0, max>,
     *     max_queue_size: int<0, max>,
     *     max_export_batch_size: int<0, max>,
     *     exporter: ComponentPlugin<SpanExporterInterface>,
     * } $properties
     */
    public function createPlugin(array $properties, Context $context): SpanProcessorInterface
    {
        return new BatchSpanProcessor(
            exporter: $properties['exporter']->create($context),
            clock: Clock::getDefault(),
            maxQueueSize: $properties['max_queue_size'],
            scheduledDelayMillis: $properties['schedule_delay'],
            exportTimeoutMillis: $properties['export_timeout'],
            maxExportBatchSize: $properties['max_export_batch_size'],
            meterProvider: $context->meterProvider,
        );
    }

    public function getConfig(ComponentProviderRegistry $registry): ArrayNodeDefinition
    {
        $node = new ArrayNodeDefinition('batch');
        $node
            ->children()
                ->integerNode('schedule_delay')->min(0)->defaultValue(5000)->end()
                ->integerNode('export_timeout')->min(0)->defaultValue(30000)->end()
                ->integerNode('max_queue_size')->min(0)->defaultValue(2048)->end()
                ->integerNode('max_export_batch_size')->min(0)->defaultValue(512)->end()
                ->append($registry->component('exporter', SpanExporterInterface::class)->isRequired())
            ->end()
        ;

        return $node;
    }
}
