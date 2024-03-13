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
    public function __construct(
        public readonly ViewProjection $view,
        public readonly Instrument $instrument,
        public readonly InstrumentationScopeInterface $instrumentationLibrary,
        public readonly ResourceInfo $resource,
        public readonly MetricStreamInterface $stream,
        public readonly MetricCollectorInterface $metricCollector,
        public readonly int $streamId,
    ) {
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
