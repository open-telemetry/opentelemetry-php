<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

interface DefaultAggregationProvider
{
    /**
     * @param string|InstrumentType $instrumentType
     */
    public function defaultAggregation($instrumentType): ?AggregationInterface;
}
