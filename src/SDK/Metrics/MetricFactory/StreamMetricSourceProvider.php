<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\MetricFactory;

use OpenTelemetry\SDK\InstrumentationScope;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Metrics\Instrument;
use OpenTelemetry\SDK\Metrics\InstrumentType;
use OpenTelemetry\SDK\Metrics\MetricMetadata;
use OpenTelemetry\SDK\Metrics\MetricSource;
use OpenTelemetry\SDK\Metrics\MetricSourceProvider;
use OpenTelemetry\SDK\Metrics\Stream\MetricStream;
use OpenTelemetry\SDK\Metrics\ViewProjection;
use OpenTelemetry\SDK\Resource;

final class StreamMetricSourceProvider implements MetricSourceProvider, MetricMetadata
{
    public function __construct(
        public readonly ViewProjection $view,
        public readonly Instrument $instrument,
        public readonly InstrumentationScope $instrumentationLibrary,
        public readonly Resource $resource,
        public readonly MetricStream $stream,
    ) {
    }

    public function create(Temporality $temporality): MetricSource
    {
        return new StreamMetricSource($this, $this->stream->register($temporality));
    }

    public function instrumentType(): InstrumentType
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

    public function temporality(): Temporality
    {
        return $this->stream->temporality();
    }
}
