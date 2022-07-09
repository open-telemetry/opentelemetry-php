<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\MetricFactory;

use OpenTelemetry\Context\ContextStorageInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesFactoryInterface;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Metrics\Instrument;
use OpenTelemetry\SDK\Metrics\MetricFactoryInterface;
use OpenTelemetry\SDK\Metrics\MetricObserver\MultiObserver;
use OpenTelemetry\SDK\Metrics\MetricObserverInterface;
use OpenTelemetry\SDK\Metrics\MetricSourceRegistryInterface;
use OpenTelemetry\SDK\Metrics\MetricWriterInterface;
use OpenTelemetry\SDK\Metrics\StalenessHandlerInterface;
use OpenTelemetry\SDK\Metrics\Stream\AsynchronousMetricStream;
use OpenTelemetry\SDK\Metrics\Stream\MetricStreamInterface;
use OpenTelemetry\SDK\Metrics\Stream\MultiStreamWriter;
use OpenTelemetry\SDK\Metrics\Stream\SynchronousMetricStream;
use OpenTelemetry\SDK\Metrics\ViewProjection;
use OpenTelemetry\SDK\Resource\ResourceInfo;

final class StreamFactory implements MetricFactoryInterface
{
    public function createAsynchronousObserver(
        ResourceInfo $resource,
        InstrumentationScopeInterface $instrumentationScope,
        Instrument $instrument,
        int $timestamp,
        iterable $views,
        AttributesFactoryInterface $attributesFactory,
        StalenessHandlerInterface $stalenessHandler,
        MetricSourceRegistryInterface $metricSourceRegistry
    ): MetricObserverInterface {
        $observer = new MultiObserver();
        foreach ($views as $view) {
            $stream = new AsynchronousMetricStream(
                $attributesFactory,
                $view->attributeProcessor,
                $view->aggregation,
                $view->exemplarReservoir,
                $observer,
                $timestamp,
            );

            $this->registerSource(
                $view,
                $instrument,
                $instrumentationScope,
                $resource,
                $stream,
                $stalenessHandler,
                $metricSourceRegistry,
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
        StalenessHandlerInterface $stalenessHandler,
        MetricSourceRegistryInterface $metricSourceRegistry,
        ?ContextStorageInterface $contextStorage = null
    ): MetricWriterInterface {
        $streams = [];
        foreach ($views as $view) {
            $stream = new SynchronousMetricStream(
                $view->attributeProcessor,
                $view->aggregation,
                $view->exemplarReservoir,
                $timestamp,
            );
            $streams[] = $stream->writable();

            $this->registerSource(
                $view,
                $instrument,
                $instrumentationScope,
                $resource,
                $stream,
                $stalenessHandler,
                $metricSourceRegistry,
            );
        }

        return new MultiStreamWriter(
            $contextStorage,
            $attributesFactory,
            $streams,
        );
    }

    private function registerSource(
        ViewProjection $view,
        Instrument $instrument,
        InstrumentationScopeInterface $instrumentationScope,
        ResourceInfo $resource,
        MetricStreamInterface $stream,
        StalenessHandlerInterface $stalenessHandler,
        MetricSourceRegistryInterface $metricSourceRegistry
    ): void {
        $provider = new StreamMetricSourceProvider(
            $view,
            $instrument,
            $instrumentationScope,
            $resource,
            $stream,
        );

        $metricSourceRegistry->add($provider, $provider, $stalenessHandler);
    }
}
