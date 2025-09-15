<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\MetricExporter;

use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Metrics\InstrumentType;
use OpenTelemetry\SDK\Metrics\MetricMetadataInterface;

trait TemporalitySelectorTrait
{
    private readonly ?Temporality $temporality;

    /**
     * Determine the temporality for a given instrument type, as a function of temporality preference.
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.48.0/specification/metrics/sdk_exporters/otlp.md#additional-environment-variable-configuration
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.48.0/specification/metrics/sdk.md#metricreader
     *
     */
    public function temporality(MetricMetadataInterface $metric): ?Temporality
    {
        return match ($this->temporality) {
            Temporality::CUMULATIVE => Temporality::CUMULATIVE,
            Temporality::DELTA => match ($metric->instrumentType()) {
                InstrumentType::UP_DOWN_COUNTER, InstrumentType::ASYNCHRONOUS_UP_DOWN_COUNTER => Temporality::CUMULATIVE,
                default => Temporality::DELTA,
            },
            default => match ($metric->instrumentType()) {
                //low-memory preference
                InstrumentType::COUNTER, InstrumentType::HISTOGRAM => Temporality::DELTA,
                default => Temporality::CUMULATIVE,
            },
        };
    }
}
