<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\MetricFactory;

use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Metrics\Instrument;
use OpenTelemetry\SDK\Metrics\MetricMetadataInterface;
use OpenTelemetry\SDK\Metrics\MetricRegistry\MetricCollectorInterface;
use OpenTelemetry\SDK\Metrics\MetricSourceInterface;
use OpenTelemetry\SDK\Metrics\MetricSourceProviderInterface;
use OpenTelemetry\SDK\Metrics\Stream\MetricStreamInterface;
use OpenTelemetry\SDK\Metrics\ViewProjection;
use OpenTelemetry\SDK\Resource\ResourceInfo;

/**
 * @internal
 */
final class StreamMetricSourceProvider implements MetricSourceProviderInterface, MetricMetadataInterface
{
    /**
     * @readonly
     */
    public ViewProjection $view;
    /**
     * @readonly
     */
    public Instrument $instrument;
    /**
     * @readonly
     */
    public InstrumentationScopeInterface $instrumentationLibrary;
    /**
     * @readonly
     */
    public ResourceInfo $resource;
    /**
     * @readonly
     */
    public MetricStreamInterface $stream;
    /**
     * @readonly
     */
    public MetricCollectorInterface $metricCollector;
    /**
     * @readonly
     */
    public int $streamId;

    public function __construct(
        ViewProjection $view,
        Instrument $instrument,
        InstrumentationScopeInterface $instrumentationLibrary,
        ResourceInfo $resource,
        MetricStreamInterface $stream,
        MetricCollectorInterface $metricCollector,
        int $streamId
    ) {
        $this->view = $view;
        $this->instrument = $instrument;
        $this->instrumentationLibrary = $instrumentationLibrary;
        $this->resource = $resource;
        $this->stream = $stream;
        $this->metricCollector = $metricCollector;
        $this->streamId = $streamId;
    }

    public function create($temporality): MetricSourceInterface
    {
        return new StreamMetricSource($this, $this->stream->register($temporality));
    }

    public function instrumentType()
    {
        return $this->instrument->type;
    }

    public function name(): string
    {
        return $this->view->name;
    }

    public function unit(): ?string
    {
        return $this->view->unit;
    }

    public function description(): ?string
    {
        return $this->view->description;
    }

    public function temporality()
    {
        return $this->stream->temporality();
    }
}
