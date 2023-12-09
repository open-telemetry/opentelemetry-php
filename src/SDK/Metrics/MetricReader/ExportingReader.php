<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\MetricReader;

use function array_keys;
use OpenTelemetry\SDK\Metrics\AggregationInterface;
use OpenTelemetry\SDK\Metrics\AggregationTemporalitySelectorInterface;
use OpenTelemetry\SDK\Metrics\DefaultAggregationProviderInterface;
use OpenTelemetry\SDK\Metrics\DefaultAggregationProviderTrait;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;
use OpenTelemetry\SDK\Metrics\MetricFactory\StreamMetricSourceProvider;
use OpenTelemetry\SDK\Metrics\MetricMetadataInterface;
use OpenTelemetry\SDK\Metrics\MetricReaderInterface;
use OpenTelemetry\SDK\Metrics\MetricRegistry\MetricCollectorInterface;
use OpenTelemetry\SDK\Metrics\MetricSourceInterface;
use OpenTelemetry\SDK\Metrics\MetricSourceProviderInterface;
use OpenTelemetry\SDK\Metrics\MetricSourceRegistryInterface;
use OpenTelemetry\SDK\Metrics\PushMetricExporterInterface;
use OpenTelemetry\SDK\Metrics\StalenessHandlerInterface;
use function spl_object_id;

final class ExportingReader implements MetricReaderInterface, MetricSourceRegistryInterface, DefaultAggregationProviderInterface
{
    use DefaultAggregationProviderTrait { defaultAggregation as private _defaultAggregation; }

    private MetricExporterInterface $exporter;
    /** @var array<int, MetricSourceInterface> */
    private array $sources = [];

    /** @var array<int, MetricCollectorInterface> */
    private array $registries = [];
    /** @var array<int, array<int, int>> */
    private array $streamIds = [];

    private bool $closed = false;

    public function __construct(MetricExporterInterface $exporter)
    {
        $this->exporter = $exporter;
    }

    public function defaultAggregation($instrumentType, array $advisory = []): ?AggregationInterface
    {
        if ($this->exporter instanceof DefaultAggregationProviderInterface) {
            /** @phan-suppress-next-line PhanParamTooMany @phpstan-ignore-next-line */
            return $this->exporter->defaultAggregation($instrumentType, $advisory);
        }

        return $this->_defaultAggregation($instrumentType, $advisory);
    }

    public function add(MetricSourceProviderInterface $provider, MetricMetadataInterface $metadata, StalenessHandlerInterface $stalenessHandler): void
    {
        if ($this->closed) {
            return;
        }
        if (!$this->exporter instanceof AggregationTemporalitySelectorInterface) {
            return;
        }
        if (!$temporality = $this->exporter->temporality($metadata)) {
            return;
        }

        $source = $provider->create($temporality);
        $sourceId = spl_object_id($source);

        $this->sources[$sourceId] = $source;
        $stalenessHandler->onStale(function () use ($sourceId): void {
            unset($this->sources[$sourceId]);
        });

        if (!$provider instanceof StreamMetricSourceProvider) {
            return;
        }

        $streamId = $provider->streamId;
        $registry = $provider->metricCollector;
        $registryId = spl_object_id($registry);

        $this->registries[$registryId] = $registry;
        $this->streamIds[$registryId][$streamId] ??= 0;
        $this->streamIds[$registryId][$streamId]++;

        $stalenessHandler->onStale(function () use ($streamId, $registryId): void {
            if (!--$this->streamIds[$registryId][$streamId]) {
                unset($this->streamIds[$registryId][$streamId]);
                if (!$this->streamIds[$registryId]) {
                    unset(
                        $this->registries[$registryId],
                        $this->streamIds[$registryId],
                    );
                }
            }
        });
    }

    private function doCollect(): bool
    {
        foreach ($this->registries as $registryId => $registry) {
            $streamIds = $this->streamIds[$registryId] ?? [];
            $registry->collectAndPush(array_keys($streamIds));
        }

        $metrics = [];
        foreach ($this->sources as $source) {
            $metrics[] = $source->collect();
        }

        if ($metrics === []) {
            return true;
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

        $collect = $this->doCollect();
        $shutdown = $this->exporter->shutdown();

        $this->sources = [];

        return $collect && $shutdown;
    }

    public function forceFlush(): bool
    {
        if ($this->closed) {
            return false;
        }
        if ($this->exporter instanceof PushMetricExporterInterface) {
            $collect = $this->doCollect();
            $forceFlush = $this->exporter->forceFlush();

            return $collect && $forceFlush;
        }

        return true;
    }
}
