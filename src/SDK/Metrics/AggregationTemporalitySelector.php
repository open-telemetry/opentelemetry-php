<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\SDK\Metrics\Data\Temporality;

/**
 * Determine the temporality for a given instrument type, as a function of temporality preference.
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.48.0/specification/metrics/sdk_exporters/otlp.md#additional-environment-variable-configuration
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.48.0/specification/metrics/sdk.md#metricreader
 *
 */
class AggregationTemporalitySelector
{
    public static function alwaysCumulative(): AggregationTemporalitySelectorInterface
    {
        return new class() implements AggregationTemporalitySelectorInterface {
            public function temporality(MetricMetadataInterface $metric): ?Temporality
            {
                return match ($metric->instrumentType()) {
                    InstrumentType::GAUGE, InstrumentType::ASYNCHRONOUS_GAUGE => Temporality::DELTA,
                    default => Temporality::CUMULATIVE,
                };
            }
        };
    }

    public static function deltaPreferred(): AggregationTemporalitySelectorInterface
    {
        return new class() implements AggregationTemporalitySelectorInterface {
            public function temporality(MetricMetadataInterface $metric): ?Temporality
            {
                return match ($metric->instrumentType()) {
                    InstrumentType::UP_DOWN_COUNTER, InstrumentType::ASYNCHRONOUS_UP_DOWN_COUNTER => Temporality::CUMULATIVE,
                    default => Temporality::DELTA,
                };
            }
        };
    }

    public static function lowMemory(): AggregationTemporalitySelectorInterface
    {
        return new class() implements AggregationTemporalitySelectorInterface {
            public function temporality(MetricMetadataInterface $metric): ?Temporality
            {
                return match ($metric->instrumentType()) {
                    InstrumentType::COUNTER, InstrumentType::HISTOGRAM, InstrumentType::GAUGE, InstrumentType::ASYNCHRONOUS_GAUGE => Temporality::DELTA,
                    default => Temporality::CUMULATIVE,
                };
            }
        };
    }
}
