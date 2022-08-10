<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\Context\ContextStorageInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesFactoryInterface;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilterInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;

/**
 * @internal
 */
interface MetricFactoryInterface
{
    /**
     * @param iterable<array{ViewProjection, MetricRegistrationInterface}> $views
     */
    public function createAsynchronousObserver(
        ResourceInfo $resource,
        InstrumentationScopeInterface $instrumentationScope,
        Instrument $instrument,
        int $timestamp,
        iterable $views,
        AttributesFactoryInterface $attributesFactory,
        ?ExemplarFilterInterface $exemplarFilter = null
    ): MetricObserverInterface;

    /**
     * @param iterable<array{ViewProjection, MetricRegistrationInterface}> $views
     */
    public function createSynchronousWriter(
        ResourceInfo $resource,
        InstrumentationScopeInterface $instrumentationScope,
        Instrument $instrument,
        int $timestamp,
        iterable $views,
        AttributesFactoryInterface $attributesFactory,
        ?ExemplarFilterInterface $exemplarFilter = null,
        ?ContextStorageInterface $contextStorage = null
    ): MetricWriterInterface;
}
