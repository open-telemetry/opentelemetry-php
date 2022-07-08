<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;

interface MetricFactoryInterface
{
    /**
     * @param iterable<ViewProjection> $views
     */
    public function createAsynchronousObserver(
        InstrumentationScopeInterface $instrumentationScope,
        Instrument $instrument,
        int $timestamp,
        iterable $views,
        StalenessHandlerInterface $stalenessHandler
    ): MetricObserverInterface;

    /**
     * @param iterable<ViewProjection> $views
     */
    public function createSynchronousWriter(
        InstrumentationScopeInterface $instrumentationScope,
        Instrument $instrument,
        int $timestamp,
        iterable $views,
        StalenessHandlerInterface $stalenessHandler
    ): MetricWriterInterface;
}
