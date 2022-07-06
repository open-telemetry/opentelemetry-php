<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;

interface MetricFactoryInterface
{

    /**
     * @return array{MetricObserverInterface, StalenessHandlerInterface&ReferenceCounterInterface}
     */
    public function createAsynchronousObserver(InstrumentationScopeInterface $instrumentationScope, Instrument $instrument, int $timestamp): array;

    /**
     * @return array{MetricWriterInterface, StalenessHandlerInterface&ReferenceCounterInterface}
     */
    public function createSynchronousWriter(InstrumentationScopeInterface $instrumentationScope, Instrument $instrument, int $timestamp): array;
}
