<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\MetricReader;

use function array_keys;
use OpenTelemetry\API\Metrics\CounterInterface;
use OpenTelemetry\API\Metrics\HistogramInterface;
use OpenTelemetry\API\Metrics\MeterProviderInterface;
use OpenTelemetry\API\Metrics\UpDownCounterInterface;
use OpenTelemetry\SDK\Metrics\AggregationInterface;
use OpenTelemetry\SDK\Metrics\AggregationTemporalitySelectorInterface;
use OpenTelemetry\SDK\Metrics\Data\DataInterface;
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
use OpenTelemetry\SDK\Metrics\MetricSourceRegistryUnregisterInterface;
use OpenTelemetry\SDK\Metrics\PushMetricExporterInterface;
use OpenTelemetry\SDK\Metrics\StalenessHandlerInterface;
use OpenTelemetry\SemConv\Incubating\Attributes\OtelIncubatingAttributes;
use OpenTelemetry\SemConv\Incubating\Metrics\OtelIncubatingMetrics;
use OpenTelemetry\SemConv\Version;
use function spl_object_id;

final class ExportingReader implements MetricReaderInterface, MetricSourceRegistryInterface, MetricSourceRegistryUnregisterInterface, DefaultAggregationProviderInterface
{
    use DefaultAggregationProviderTrait { defaultAggregation as private _defaultAggregation; }
    /** @var array<int, MetricSourceInterface> */
    private array $sources = [];

    /** @var array<int, MetricCollectorInterface> */
    private array $registries = [];
    /** @var array<int, array<int, list<int>>> */
    private array $streamIds = [];

    private bool $closed = false;

    private readonly ?HistogramInterface $collectionDuration;
    private readonly ?UpDownCounterInterface $dataPointInflightCounter;
    private readonly ?CounterInterface $dataPointExportedCounter;

    /** @var array<non-empty-string, string> */
    private readonly array $readerAttributes;
    /** @var array<non-empty-string, string> */
    private readonly array $exporterAttributes;

    public function __construct(
        private readonly MetricExporterInterface $exporter,
        ?MeterProviderInterface $meterProvider = null,
    ) {
        $this->readerAttributes = [
            OtelIncubatingAttributes::OTEL_COMPONENT_TYPE => OtelIncubatingAttributes::OTEL_COMPONENT_TYPE_VALUE_PERIODIC_METRIC_READER,
            OtelIncubatingAttributes::OTEL_COMPONENT_NAME => OtelIncubatingAttributes::OTEL_COMPONENT_TYPE_VALUE_PERIODIC_METRIC_READER . '/' . spl_object_id($this),
        ];

        if ($meterProvider === null) {
            $this->exporterAttributes = [];
            $this->collectionDuration = null;
            $this->dataPointInflightCounter = null;
            $this->dataPointExportedCounter = null;
        } else {
            $this->exporterAttributes = [
                OtelIncubatingAttributes::OTEL_COMPONENT_NAME => (new \ReflectionClass($this->exporter))->getShortName(),
            ];

            $meter = $meterProvider->getMeter('io.opentelemetry.sdk', schemaUrl: Version::VERSION_1_36_0->url());
            $this->collectionDuration = $meter->createHistogram(
                OtelIncubatingMetrics::OTEL_SDK_METRIC_READER_COLLECTION_DURATION,
                's',
                'The duration of the collect operation of the metric reader',
            );
            $this->dataPointInflightCounter = $meter->createUpDownCounter(
                OtelIncubatingMetrics::OTEL_SDK_EXPORTER_METRIC_DATA_POINT_INFLIGHT,
                '{data_point}',
                'The number of metric data points which were passed to the exporter, but that have not been exported yet',
            );
            $this->dataPointExportedCounter = $meter->createCounter(
                OtelIncubatingMetrics::OTEL_SDK_EXPORTER_METRIC_DATA_POINT_EXPORTED,
                '{data_point}',
                'The number of metric data points for which the export has finished, either successful or failed',
            );
        }
    }

    private function countDataPoints(DataInterface $data): int
    {
        return $data->dataPointCount();
    }

    #[\Override]
    public function defaultAggregation($instrumentType, array $advisory = []): ?AggregationInterface
    {
        if ($this->exporter instanceof DefaultAggregationProviderInterface) {
            /** @phan-suppress-next-line PhanParamTooMany @phpstan-ignore-next-line */
            return $this->exporter->defaultAggregation($instrumentType, $advisory);
        }

        return $this->_defaultAggregation($instrumentType, $advisory);
    }

    #[\Override]
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
        if (!$provider instanceof StreamMetricSourceProvider) {
            $stalenessHandler->onStale(function () use ($sourceId): void {
                unset($this->sources[$sourceId]);
            });

            return;
        }

        $streamId = $provider->streamId;
        $registry = $provider->metricCollector;
        $registryId = spl_object_id($registry);

        $this->registries[$registryId] = $registry;
        $this->streamIds[$registryId][$streamId][] = $sourceId;
    }

    #[\Override]
    public function unregisterStream(MetricCollectorInterface $collector, int $streamId): void
    {
        $registryId = spl_object_id($collector);
        foreach ($this->streamIds[$registryId][$streamId] ?? [] as $sourceId) {
            unset($this->sources[$sourceId]);
        }
        unset($this->streamIds[$registryId][$streamId]);
        if (!$this->streamIds[$registryId]) {
            unset(
                $this->registries[$registryId],
                $this->streamIds[$registryId],
            );
        }
    }

    private function doCollect(): bool
    {
        $startNs = hrtime(true);

        foreach ($this->registries as $registryId => $registry) {
            $streamIds = $this->streamIds[$registryId] ?? [];
            $registry->collectAndPush(array_keys($streamIds));
        }

        $metrics = [];
        foreach ($this->sources as $source) {
            $metrics[] = $source->collect();
        }

        $durationSeconds = (hrtime(true) - $startNs) / 1_000_000_000;
        $this->collectionDuration?->record($durationSeconds, $this->readerAttributes);

        if ($metrics === []) {
            return true;
        }

        $dataPointCount = 0;
        foreach ($metrics as $metric) {
            /** @psalm-suppress RedundantCondition */
            if (isset($metric->data)) {
                $dataPointCount += $this->countDataPoints($metric->data);
            }
        }

        $this->dataPointInflightCounter?->add($dataPointCount, $this->exporterAttributes);
        $result = false;

        try {
            $result = $this->exporter->export($metrics);
            if ($result) {
                $this->dataPointExportedCounter?->add($dataPointCount, $this->exporterAttributes);
            } else {
                // '_OTHER' is the semconv catch-all for errors with no specific exception class or protocol code
                $this->dataPointExportedCounter?->add($dataPointCount, $this->exporterAttributes + ['error.type' => '_OTHER']);
            }
        } finally {
            $this->dataPointInflightCounter?->add(-$dataPointCount, $this->exporterAttributes);
        }

        return $result;
    }

    #[\Override]
    public function collect(): bool
    {
        if ($this->closed) {
            return false;
        }

        return $this->doCollect();
    }

    #[\Override]
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

    #[\Override]
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
