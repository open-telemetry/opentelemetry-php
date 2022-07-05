<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\SDK\InstrumentationScope;

interface MetricFactory
{

    /**
     * @return array{MetricObserver, StalenessHandler&ReferenceCounter}
     */
    public function createAsynchronousObserver(InstrumentationScope $instrumentationScope, Instrument $instrument, int $timestamp): array;

    /**
     * @return array{MetricWriter, StalenessHandler&ReferenceCounter}
     */
    public function createSynchronousWriter(InstrumentationScope $instrumentationScope, Instrument $instrument, int $timestamp): array;
}
