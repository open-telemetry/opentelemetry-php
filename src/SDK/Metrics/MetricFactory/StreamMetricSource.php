<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\MetricFactory;

use OpenTelemetry\SDK\Metrics\Data\Metric;
use OpenTelemetry\SDK\Metrics\MetricSourceInterface;

/**
 * @internal
 */
final class StreamMetricSource implements MetricSourceInterface
{
    private StreamMetricSourceProvider $provider;
    private int $reader;
    public function __construct(StreamMetricSourceProvider $provider, int $reader)
    {
        $this->provider = $provider;
        $this->reader = $reader;
    }

    public function collectionTimestamp(): int
    {
        return $this->provider->stream->timestamp();
    }

    public function collect(): Metric
    {
        return new Metric(
            $this->provider->instrumentationLibrary,
            $this->provider->resource,
            $this->provider->view->name,
            $this->provider->view->unit,
            $this->provider->view->description,
            $this->provider->stream->collect($this->reader),
        );
    }

    public function __destruct()
    {
        $this->provider->stream->unregister($this->reader);
    }
}
