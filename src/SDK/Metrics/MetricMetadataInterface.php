<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\SDK\Metrics\Data\Temporality;

interface MetricMetadataInterface
{
    /**
     * @return string|InstrumentType
     */
    public function instrumentType();

    public function name(): string;

    public function unit(): ?string;

    public function description(): ?string;

    /**
     * Returns the underlying temporality of this metric.
     *
     * @return string|Temporality internal temporality
     */
    public function temporality();
}
