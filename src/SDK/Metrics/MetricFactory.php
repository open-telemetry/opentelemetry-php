<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;

interface MetricFactory
{

    /**
     * @return array{MetricObserver, StalenessHandler&ReferenceCounter}
     */
    public function createAsynchronousObserver(InstrumentationScopeInterface $instrumentationScope, Instrument $instrument, int $timestamp): array;

    /**
     * @return array{MetricWriter, StalenessHandler&ReferenceCounter}
     */
    public function createSynchronousWriter(InstrumentationScopeInterface $instrumentationScope, Instrument $instrument, int $timestamp): array;
}
