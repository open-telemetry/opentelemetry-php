<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\MetricFactory;

use OpenTelemetry\Context\ContextStorageInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesFactoryInterface;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Metrics\Instrument;
use OpenTelemetry\SDK\Metrics\MetricFactory;
use OpenTelemetry\SDK\Metrics\MetricObserver\MultiObserver;
use OpenTelemetry\SDK\Metrics\MetricSourceRegistry;
use OpenTelemetry\SDK\Metrics\StalenessHandler;
use OpenTelemetry\SDK\Metrics\StalenessHandlerFactory;
use OpenTelemetry\SDK\Metrics\Stream\AsynchronousMetricStream;
use OpenTelemetry\SDK\Metrics\Stream\MetricStream;
use OpenTelemetry\SDK\Metrics\Stream\MultiStreamWriter;
use OpenTelemetry\SDK\Metrics\Stream\SynchronousMetricStream;
use OpenTelemetry\SDK\Metrics\ViewProjection;
use OpenTelemetry\SDK\Metrics\ViewRegistry;
use OpenTelemetry\SDK\Resource\ResourceInfo;

final class StreamFactory implements MetricFactory
{
    private ?ContextStorageInterface $contextStorage;
    private ResourceInfo $resource;

    private ViewRegistry $views;
    private MetricSourceRegistry $metricSources;
    private AttributesFactoryInterface $attributesFactory;
    private StalenessHandlerFactory $stalenessHandlerFactory;

    public function __construct(
        ?ContextStorageInterface $contextStorage,
        ResourceInfo $resource,
        ViewRegistry $views,
        MetricSourceRegistry $metricSources,
        AttributesFactoryInterface $attributesFactory,
        StalenessHandlerFactory $stalenessHandlerFactory
    ) {
        $this->contextStorage = $contextStorage;
        $this->resource = $resource;
        $this->views = $views;
        $this->metricSources = $metricSources;
        $this->attributesFactory = $attributesFactory;
        $this->stalenessHandlerFactory = $stalenessHandlerFactory;
    }

    public function createAsynchronousObserver(InstrumentationScopeInterface $instrumentationScope, Instrument $instrument, int $timestamp): array
    {
        $views = $this->views->find(
            $instrument,
            $instrumentationScope,
        );

        $stalenessHandler = $this->stalenessHandlerFactory->create();
        $observer = new MultiObserver($stalenessHandler);
        foreach ($views as $view) {
            $stream = new AsynchronousMetricStream(
                $this->attributesFactory,
                $view->attributeProcessor,
                $view->aggregation,
                $view->exemplarReservoir,
                $observer,
                $timestamp,
            );
            $this->registerSource($view, $instrument, $instrumentationScope, $stream, $stalenessHandler);
        }

        return [$observer, $stalenessHandler];
    }

    public function createSynchronousWriter(InstrumentationScopeInterface $instrumentationScope, Instrument $instrument, int $timestamp): array
    {
        $views = $this->views->find(
            $instrument,
            $instrumentationScope,
        );

        $stalenessHandler = $this->stalenessHandlerFactory->create();
        $streams = [];
        foreach ($views as $view) {
            $stream = new SynchronousMetricStream(
                $view->attributeProcessor,
                $view->aggregation,
                $view->exemplarReservoir,
                $timestamp,
            );
            $this->registerSource($view, $instrument, $instrumentationScope, $stream, $stalenessHandler);

            $streams[] = $stream->writable();
        }

        $writer = new MultiStreamWriter(
            $this->contextStorage,
            $this->attributesFactory,
            $streams,
        );

        return [$writer, $stalenessHandler];
    }

    private function registerSource(
        ViewProjection $view,
        Instrument $instrument,
        InstrumentationScopeInterface $instrumentationScope,
        MetricStream $stream,
        StalenessHandler $stalenessHandler
    ): void {
        $provider = new StreamMetricSourceProvider(
            $view,
            $instrument,
            $instrumentationScope,
            $this->resource,
            $stream,
        );

        $this->metricSources->add($provider, $provider, $stalenessHandler);
    }
}
