<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

interface DefaultAggregationProviderInterface
{
    /**
     * @param InstrumentType $instrumentType
     * @param array $advisory optional set of recommendations
     */
    public function defaultAggregation(InstrumentType $instrumentType, array $advisory = []): ?AggregationInterface;
}
