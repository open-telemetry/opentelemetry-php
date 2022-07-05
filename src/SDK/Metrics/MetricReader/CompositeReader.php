<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\MetricReader;

use OpenTelemetry\SDK\Metrics\MetricMetadata;
use OpenTelemetry\SDK\Metrics\MetricReader;
use OpenTelemetry\SDK\Metrics\MetricSourceProvider;
use OpenTelemetry\SDK\Metrics\MetricSourceRegistry;
use OpenTelemetry\SDK\Metrics\StalenessHandler;

final class CompositeReader implements MetricReader, MetricSourceRegistry
{
    private iterable $metricReaders;

    private bool $closed = false;

    /**
     * @param iterable<MetricReader&MetricSourceRegistry> $metricReaders
     */
    public function __construct(iterable $metricReaders)
    {
        $this->metricReaders = $metricReaders;
    }

    public function add(MetricSourceProvider&MetricMetadata $provider, StalenessHandler $stalenessHandler): void
    {
        if ($this->closed) {
            return;
        }

        foreach ($this->metricReaders as $metricReader) {
            $metricReader->add($provider, $stalenessHandler);
        }
    }

    public function collect(): bool
    {
        if ($this->closed) {
            return false;
        }

        $success = true;
        foreach ($this->metricReaders as $metricReader) {
            $success &= $metricReader->collect();
        }

        return (bool) $success;
    }

    public function shutdown(): bool
    {
        if ($this->closed) {
            return false;
        }

        $this->closed = true;

        $success = true;
        foreach ($this->metricReaders as $metricReader) {
            $success &= $metricReader->shutdown();
        }

        return (bool) $success;
    }

    public function forceFlush(): bool
    {
        if ($this->closed) {
            return false;
        }

        $success = true;
        foreach ($this->metricReaders as $metricReader) {
            $success &= $metricReader->forceFlush();
        }

        return (bool) $success;
    }
}
