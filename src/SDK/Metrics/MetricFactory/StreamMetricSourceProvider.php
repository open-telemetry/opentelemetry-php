<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\MetricFactory;

use OpenTelemetry\SDK\InstrumentationScope;
use OpenTelemetry\SDK\Metrics\Instrument;
use OpenTelemetry\SDK\Metrics\MetricMetadata;
use OpenTelemetry\SDK\Metrics\MetricSource;
use OpenTelemetry\SDK\Metrics\MetricSourceProvider;
use OpenTelemetry\SDK\Metrics\Stream\MetricStream;
use OpenTelemetry\SDK\Metrics\ViewProjection;
use OpenTelemetry\SDK\Resource;

final class StreamMetricSourceProvider implements MetricSourceProvider, MetricMetadata
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
    public InstrumentationScope $instrumentationLibrary;
    /**
     * @readonly
     */
    public Resource $resource;
    /**
     * @readonly
     */
    public MetricStream $stream;
    public function __construct(ViewProjection $view, Instrument $instrument, InstrumentationScope $instrumentationLibrary, Resource $resource, MetricStream $stream)
    {
        $this->view = $view;
        $this->instrument = $instrument;
        $this->instrumentationLibrary = $instrumentationLibrary;
        $this->resource = $resource;
        $this->stream = $stream;
    }

    public function create($temporality): MetricSource
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
