<?php declare(strict_types=1);
namespace OpenTelemetry\SDK\Metrics\MetricFactory;

use OpenTelemetry\SDK\Metrics\Data\Metric;
use OpenTelemetry\SDK\Metrics\MetricSource;

final class StreamMetricSource implements MetricSource {

    public function __construct(
        private StreamMetricSourceProvider $provider,
        private int $reader,
    ) {}

    public function collectionTimestamp(): int {
        return $this->provider->stream->collectionTimestamp();
    }

    public function collect(?int $timestamp): Metric {
        return new Metric(
            $this->provider->instrumentationLibrary,
            $this->provider->resource,
            $this->provider->view->name,
            $this->provider->view->description,
            $this->provider->view->unit,
            $this->provider->stream->collect($this->reader, $timestamp),
        );
    }

    public function __destruct() {
        $this->provider->stream->unregister($this->reader);
    }
}
