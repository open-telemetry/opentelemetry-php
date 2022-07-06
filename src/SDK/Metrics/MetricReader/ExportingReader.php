<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\MetricReader;

use OpenTelemetry\SDK\Common\Time\ClockInterface;
use OpenTelemetry\SDK\Metrics\MetricExporter;
use OpenTelemetry\SDK\Metrics\MetricMetadata;
use OpenTelemetry\SDK\Metrics\MetricReader;
use OpenTelemetry\SDK\Metrics\MetricSource;
use OpenTelemetry\SDK\Metrics\MetricSourceProvider;
use OpenTelemetry\SDK\Metrics\MetricSourceRegistry;
use OpenTelemetry\SDK\Metrics\StalenessHandler;
use function spl_object_id;

final class ExportingReader implements MetricReader, MetricSourceRegistry
{
    private MetricExporter $exporter;
    private ClockInterface $clock;
    /** @var array<int, MetricSource> */
    private array $sources = [];

    private bool $closed = false;

    public function __construct(MetricExporter $exporter, ClockInterface $clock)
    {
        $this->exporter = $exporter;
        $this->clock = $clock;
    }

    public function add(MetricSourceProvider $provider, MetricMetadata $metadata, StalenessHandler $stalenessHandler): void
    {
        if (!$temporality = $this->exporter->temporality($metadata)) {
            return;
        }

        $source = $provider->create($temporality);
        $sourceId = spl_object_id($source);

        $this->sources[$sourceId] = $source;

        $stalenessHandler->onStale(function () use ($sourceId): void {
            unset($this->sources[$sourceId]);
        });
    }

    private function doCollect(): bool
    {
        $timestamp = $this->clock->now();
        $metrics = [];
        foreach ($this->sources as $source) {
            $metrics[] = $source->collect($timestamp);
        }

        return $this->exporter->export($metrics);
    }

    public function collect(): bool
    {
        if ($this->closed) {
            return false;
        }

        return $this->doCollect();
    }

    public function shutdown(): bool
    {
        if ($this->closed) {
            return false;
        }

        $this->closed = true;

        return (bool) (+$this->doCollect() & +$this->exporter->shutdown());
    }

    public function forceFlush(): bool
    {
        if ($this->closed) {
            return false;
        }

        return (bool) (+$this->doCollect() & +$this->exporter->forceFlush());
    }
}
