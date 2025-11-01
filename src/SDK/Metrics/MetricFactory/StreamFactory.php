<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\MetricFactory;

use function array_keys;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Metrics\Aggregation\ExplicitBucketHistogramAggregation;
use OpenTelemetry\SDK\Metrics\AggregationInterface;
use OpenTelemetry\SDK\Metrics\AttributeProcessor\FilteredAttributeProcessor;
use OpenTelemetry\SDK\Metrics\AttributeProcessorInterface;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilterInterface;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarReservoirInterface;
use OpenTelemetry\SDK\Metrics\Exemplar\FilteredReservoir;
use OpenTelemetry\SDK\Metrics\Exemplar\FixedSizeReservoir;
use OpenTelemetry\SDK\Metrics\Exemplar\HistogramBucketReservoir;
use OpenTelemetry\SDK\Metrics\Instrument;
use OpenTelemetry\SDK\Metrics\MetricFactoryInterface;
use OpenTelemetry\SDK\Metrics\MetricRegistrationInterface;
use OpenTelemetry\SDK\Metrics\MetricRegistry\MetricCollectorInterface;
use OpenTelemetry\SDK\Metrics\MetricRegistry\MetricRegistryInterface;
use OpenTelemetry\SDK\Metrics\Stream\AsynchronousMetricStream;
use OpenTelemetry\SDK\Metrics\Stream\MetricAggregator;
use OpenTelemetry\SDK\Metrics\Stream\MetricAggregatorFactory;
use OpenTelemetry\SDK\Metrics\Stream\MetricStreamInterface;
use OpenTelemetry\SDK\Metrics\Stream\SynchronousMetricStream;
use OpenTelemetry\SDK\Metrics\ViewProjection;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use function serialize;
use function spl_object_id;
use Throwable;

/**
 * @internal
 */
final class StreamFactory implements MetricFactoryInterface
{
    #[\Override]
    public function createAsynchronousObserver(
        MetricRegistryInterface $registry,
        ResourceInfo $resource,
        InstrumentationScopeInterface $instrumentationScope,
        Instrument $instrument,
        int $timestamp,
        iterable $views,
    ): array {
        $streams = [];
        $dedup = [];
        foreach ($views as [$view, $registration]) {
            if ($view->aggregation === null) {
                continue;
            }

            $dedupId = $this->streamId($view->aggregation, $view->attributeKeys);
            if (($streamId = $dedup[$dedupId] ?? null) === null) {
                $stream = new AsynchronousMetricStream($view->aggregation, $timestamp);
                $streamId = $registry->registerAsynchronousStream($instrument, $stream, new MetricAggregatorFactory(
                    $this->attributeProcessor($view->attributeKeys),
                    $view->aggregation,
                ));

                $streams[$streamId] = $stream;
                $dedup[$dedupId] = $streamId;
            }

            $this->registerSource(
                $view,
                $instrument,
                $instrumentationScope,
                $resource,
                $streams[$streamId],
                $registry,
                $registration,
                $streamId,
            );
        }

        return array_keys($streams);
    }

    #[\Override]
    public function createSynchronousWriter(
        MetricRegistryInterface $registry,
        ResourceInfo $resource,
        InstrumentationScopeInterface $instrumentationScope,
        Instrument $instrument,
        int $timestamp,
        iterable $views,
        ?ExemplarFilterInterface $exemplarFilter = null,
    ): array {
        $streams = [];
        $dedup = [];
        foreach ($views as [$view, $registration]) {
            if ($view->aggregation === null) {
                continue;
            }

            $dedupId = $this->streamId($view->aggregation, $view->attributeKeys);
            if (($streamId = $dedup[$dedupId] ?? null) === null) {
                $stream = new SynchronousMetricStream($view->aggregation, $timestamp);
                $streamId = $registry->registerSynchronousStream($instrument, $stream, new MetricAggregator(
                    $this->attributeProcessor($view->attributeKeys),
                    $view->aggregation,
                    $this->createExemplarReservoir($view->aggregation, $exemplarFilter),
                ));

                $streams[$streamId] = $stream;
                $dedup[$dedupId] = $streamId;
            }

            $this->registerSource(
                $view,
                $instrument,
                $instrumentationScope,
                $resource,
                $streams[$streamId],
                $registry,
                $registration,
                $streamId,
            );
        }

        return array_keys($streams);
    }

    private function attributeProcessor(
        ?array $attributeKeys,
    ): ?AttributeProcessorInterface {
        return $attributeKeys !== null
            ? new FilteredAttributeProcessor($attributeKeys)
            : null;
    }

    private function createExemplarReservoir(
        AggregationInterface $aggregation,
        ?ExemplarFilterInterface $exemplarFilter,
    ): ?ExemplarReservoirInterface {
        if (!$exemplarFilter) {
            return null;
        }

        if ($aggregation instanceof ExplicitBucketHistogramAggregation && $aggregation->boundaries) {
            $exemplarReservoir = new HistogramBucketReservoir($aggregation->boundaries);
        } else {
            $exemplarReservoir = new FixedSizeReservoir();
        }

        return new FilteredReservoir($exemplarReservoir, $exemplarFilter);
    }

    private function registerSource(
        ViewProjection $view,
        Instrument $instrument,
        InstrumentationScopeInterface $instrumentationScope,
        ResourceInfo $resource,
        MetricStreamInterface $stream,
        MetricCollectorInterface $metricCollector,
        MetricRegistrationInterface $metricRegistration,
        int $streamId,
    ): void {
        $provider = new StreamMetricSourceProvider(
            $view,
            $instrument,
            $instrumentationScope,
            $resource,
            $stream,
            $metricCollector,
            $streamId,
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
        } catch (Throwable) {
        }

        return spl_object_id($object);
    }
}
