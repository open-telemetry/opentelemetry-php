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
    public function __construct(
        private readonly StreamMetricSourceProvider $provider,
        private readonly int $reader,
    ) {
    }

    #[\Override]
    public function collectionTimestamp(): int
    {
        return $this->provider->stream->timestamp();
    }

    #[\Override]
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
