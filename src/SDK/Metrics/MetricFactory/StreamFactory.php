<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\MetricFactory;

use OpenTelemetry\Context\ContextStorageInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesFactoryInterface;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Metrics\AggregationInterface;
use OpenTelemetry\SDK\Metrics\AttributeProcessor\FilteredAttributeProcessor;
use OpenTelemetry\SDK\Metrics\AttributeProcessorInterface;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilterInterface;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarReservoirInterface;
use OpenTelemetry\SDK\Metrics\Exemplar\FilteredReservoir;
use OpenTelemetry\SDK\Metrics\Instrument;
use OpenTelemetry\SDK\Metrics\MetricFactoryInterface;
use OpenTelemetry\SDK\Metrics\MetricObserver\MultiObserver;
use OpenTelemetry\SDK\Metrics\MetricObserverInterface;
use OpenTelemetry\SDK\Metrics\MetricRegistrationInterface;
use OpenTelemetry\SDK\Metrics\MetricWriterInterface;
use OpenTelemetry\SDK\Metrics\Stream\AsynchronousMetricCollector;
use OpenTelemetry\SDK\Metrics\Stream\AsynchronousMetricStream;
use OpenTelemetry\SDK\Metrics\Stream\MetricAggregator;
use OpenTelemetry\SDK\Metrics\Stream\MetricCollectorInterface;
use OpenTelemetry\SDK\Metrics\Stream\MetricStreamInterface;
use OpenTelemetry\SDK\Metrics\Stream\MultiStreamWriter;
use OpenTelemetry\SDK\Metrics\Stream\SynchronousMetricCollector;
use OpenTelemetry\SDK\Metrics\Stream\SynchronousMetricStream;
use OpenTelemetry\SDK\Metrics\ViewProjection;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use function assert;
use function serialize;
use function spl_object_id;
use Throwable;

/**
 * @internal
 */
final class StreamFactory implements MetricFactoryInterface
{
    public function createAsynchronousObserver(
        ResourceInfo $resource,
        InstrumentationScopeInterface $instrumentationScope,
        Instrument $instrument,
        int $timestamp,
        iterable $views,
        AttributesFactoryInterface $attributesFactory,
        ?ExemplarFilterInterface $exemplarFilter = null
    ): MetricObserverInterface {
        $observer = new MultiObserver();
        $dedup = [];
        foreach ($views as [$view, $registry]) {
            if ($view->aggregation === null) {
                continue;
            }

            $streamId = $this->streamId($view->aggregation, $view->attributeKeys);
            if (([$stream, $collector] = $dedup[$streamId] ?? null) === null) {
                $collector = new AsynchronousMetricCollector(
                    $observer,
                    $this->attributeProcessor($view->attributeKeys, $attributesFactory),
                    $view->aggregation,
                    $attributesFactory,
                );
                $stream = new AsynchronousMetricStream($view->aggregation, $timestamp);

                $dedup[$streamId] = [$stream, $collector];
            }

            /** @psalm-suppress PossiblyNullArgument */
            $this->registerSource(
                $view,
                $instrument,
                $instrumentationScope,
                $resource,
                $stream,
                $collector,
                $registry,
            );
        }

        return $observer;
    }

    public function createSynchronousWriter(
        ResourceInfo $resource,
        InstrumentationScopeInterface $instrumentationScope,
        Instrument $instrument,
        int $timestamp,
        iterable $views,
        AttributesFactoryInterface $attributesFactory,
        ?ExemplarFilterInterface $exemplarFilter = null,
        ?ContextStorageInterface $contextStorage = null
    ): MetricWriterInterface {
        $streams = [];
        $dedup = [];
        foreach ($views as [$view, $registry]) {
            if ($view->aggregation === null) {
                continue;
            }

            $streamId = $this->streamId($view->aggregation, $view->attributeKeys);
            if (([$stream, $collector] = $dedup[$streamId] ?? null) === null) {
                $aggregator = new MetricAggregator(
                    $this->attributeProcessor($view->attributeKeys, $attributesFactory),
                    $view->aggregation,
                    $this->applyExemplarFilter(
                        $view->aggregation->exemplarReservoir($attributesFactory),
                        $exemplarFilter,
                    ),
                );
                $collector = new SynchronousMetricCollector($aggregator);
                $stream = new SynchronousMetricStream($view->aggregation, $timestamp);
                $streams[] = $aggregator;

                $dedup[$streamId] = [$stream, $collector];
            }

            /** @psalm-suppress PossiblyNullArgument */
            $this->registerSource(
                $view,
                $instrument,
                $instrumentationScope,
                $resource,
                $stream,
                $collector,
                $registry,
            );
        }

        return new MultiStreamWriter(
            $contextStorage,
            $attributesFactory,
            $streams,
        );
    }

    private function attributeProcessor(
        ?array $attributeKeys,
        AttributesFactoryInterface $attributesFactory
    ): ?AttributeProcessorInterface {
        return $attributeKeys !== null
            ? new FilteredAttributeProcessor($attributesFactory, $attributeKeys)
            : null;
    }

    private function applyExemplarFilter(
        ?ExemplarReservoirInterface $exemplarReservoir,
        ?ExemplarFilterInterface $exemplarFilter
    ): ?ExemplarReservoirInterface {
        return $exemplarReservoir !== null && $exemplarFilter !== null
            ? new FilteredReservoir($exemplarReservoir, $exemplarFilter)
            : $exemplarReservoir;
    }

    private function registerSource(
        ViewProjection $view,
        Instrument $instrument,
        InstrumentationScopeInterface $instrumentationScope,
        ResourceInfo $resource,
        MetricStreamInterface $stream,
        MetricCollectorInterface $metricCollector,
        MetricRegistrationInterface $metricRegistration
    ): void {
        $provider = new StreamMetricSourceProvider(
            $view,
            $instrument,
            $instrumentationScope,
            $resource,
            $stream,
            $metricCollector,
        );

        $metricRegistration->register($provider, $provider);
    }

    private function streamId(AggregationInterface $aggregation, ?array $attributeKeys): string
    {
        return $this->trySerialize($aggregation) . serialize($attributeKeys);
    }

    private function trySerialize(object $object)
    {
        try {
            return serialize($object);
        } catch (Throwable $e) {
        }

        return spl_object_id($object);
    }
}
