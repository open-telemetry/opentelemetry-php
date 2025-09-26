<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

interface MetricMetadataInterface
{
    /**
     * @return InstrumentType
     */
    public function instrumentType(): InstrumentType;

    public function name(): string;

    public function unit(): ?string;

    public function description(): ?string;
}
