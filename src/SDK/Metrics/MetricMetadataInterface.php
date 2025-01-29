<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\SDK\Metrics\Data\Temporality;

interface MetricMetadataInterface
{
    /**
     * @return InstrumentType
     */
    public function instrumentType(): InstrumentType;

    public function name(): string;

    public function unit(): ?string;

    public function description(): ?string;

    /**
     * Returns the underlying temporality of this metric.
     *
     * @return ?Temporality internal temporality
     */
    public function temporality(): ?Temporality;
}
