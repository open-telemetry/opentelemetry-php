<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\MetricReader;

use OpenTelemetry\SDK\Common\Time\ClockInterface;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;
use OpenTelemetry\SDK\Metrics\MetricMetadataInterface;
use OpenTelemetry\SDK\Metrics\MetricReaderInterface;
use OpenTelemetry\SDK\Metrics\MetricSourceInterface;
use OpenTelemetry\SDK\Metrics\MetricSourceProviderInterface;
use OpenTelemetry\SDK\Metrics\MetricSourceRegistryInterface;
use OpenTelemetry\SDK\Metrics\StalenessHandlerInterface;
use function spl_object_id;

final class ExportingReader implements MetricReaderInterface, MetricSourceRegistryInterface
{
    private MetricExporterInterface $exporter;
    private ClockInterface $clock;
    /** @var array<int, MetricSourceInterface> */
    private array $sources = [];

    private bool $closed = false;

    public function __construct(MetricExporterInterface $exporter, ClockInterface $clock)
    {
        $this->exporter = $exporter;
        $this->clock = $clock;
    }

    public function add(MetricSourceProviderInterface $provider, MetricMetadataInterface $metadata, StalenessHandlerInterface $stalenessHandler): void
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
