<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\SDK\Metrics\Data\Temporality;

interface MetricMetadata
{
    /**
     * @return string|InstrumentType
     */
    public function instrumentType();

    public function name(): string;

    public function unit(): ?string;

    public function description(): ?string;

    /**
     * @return string|Temporality
     */
    public function temporality();
}
