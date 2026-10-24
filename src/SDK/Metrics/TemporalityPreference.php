<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\SDK\Metrics\Data\Temporality;

/**
 * Chooses aggregation temporality by instrument kind, as for
 * `OTEL_EXPORTER_OTLP_METRICS_TEMPORALITY_PREFERENCE`.
 *
 * @see https://opentelemetry.io/docs/specs/otel/metrics/sdk_exporters/otlp/#additional-environment-variable-configuration
 */
final class TemporalityPreference implements AggregationTemporalitySelectorInterface
{
    /**
     * @param list<string|InstrumentType> $deltaInstrumentTypes
     */
    private function __construct(private readonly array $deltaInstrumentTypes)
    {
    }

    public static function cumulative(): self
    {
        return new self([]);
    }

    public static function delta(): self
    {
        return new self([
            InstrumentType::COUNTER,
            InstrumentType::HISTOGRAM,
            InstrumentType::GAUGE,
            InstrumentType::ASYNCHRONOUS_COUNTER,
            InstrumentType::ASYNCHRONOUS_GAUGE,
        ]);
    }

    public static function lowMemory(): self
    {
        return new self([
            InstrumentType::COUNTER,
            InstrumentType::HISTOGRAM,
        ]);
    }

    #[\Override]
    public function temporality(MetricMetadataInterface $metric): string
    {
        return in_array($metric->instrumentType(), $this->deltaInstrumentTypes, true)
            ? Temporality::DELTA
            : Temporality::CUMULATIVE;
    }
}
