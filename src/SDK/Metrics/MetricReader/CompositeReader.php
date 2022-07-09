<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\MetricReader;

use OpenTelemetry\SDK\Metrics\MetricMetadataInterface;
use OpenTelemetry\SDK\Metrics\MetricReaderInterface;
use OpenTelemetry\SDK\Metrics\MetricSourceProviderInterface;
use OpenTelemetry\SDK\Metrics\MetricSourceRegistryInterface;
use OpenTelemetry\SDK\Metrics\StalenessHandlerInterface;

final class CompositeReader implements MetricReaderInterface, MetricSourceRegistryInterface
{
    private iterable $metricReaders;

    private bool $closed = false;

    /**
     * @param iterable<MetricReaderInterface&MetricSourceRegistryInterface> $metricReaders
     */
    public function __construct(iterable $metricReaders)
    {
        $this->metricReaders = $metricReaders;
    }

    public function add(MetricSourceProviderInterface $provider, MetricMetadataInterface $metadata, StalenessHandlerInterface $stalenessHandler): void
    {
        if ($this->closed) {
            return;
        }

        foreach ($this->metricReaders as $metricReader) {
            $metricReader->add($provider, $metadata, $stalenessHandler);
        }
    }

    public function collect(): bool
    {
        if ($this->closed) {
            return false;
        }

        $success = true;
        foreach ($this->metricReaders as $metricReader) {
            if (!$metricReader->collect()) {
                $success = false;
            }
        }

        return $success;
    }

    public function shutdown(): bool
    {
        if ($this->closed) {
            return false;
        }

        $this->closed = true;

        $success = true;
        foreach ($this->metricReaders as $metricReader) {
            if (!$metricReader->shutdown()) {
                $success = false;
            }
        }

        return $success;
    }

    public function forceFlush(): bool
    {
        if ($this->closed) {
            return false;
        }

        $success = true;
        foreach ($this->metricReaders as $metricReader) {
            if (!$metricReader->forceFlush()) {
                $success = false;
            }
        }

        return $success;
    }
}
