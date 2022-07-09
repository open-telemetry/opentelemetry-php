<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\Context\ContextStorageInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesFactoryInterface;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;

interface MetricFactoryInterface
{
    /**
     * @param iterable<ViewProjection> $views
     */
    public function createAsynchronousObserver(
        ResourceInfo $resource,
        InstrumentationScopeInterface $instrumentationScope,
        Instrument $instrument,
        int $timestamp,
        iterable $views,
        AttributesFactoryInterface $attributesFactory,
        StalenessHandlerInterface $stalenessHandler,
        MetricSourceRegistryInterface $metricSourceRegistry
    ): MetricObserverInterface;

    /**
     * @param iterable<ViewProjection> $views
     */
    public function createSynchronousWriter(
        ResourceInfo $resource,
        InstrumentationScopeInterface $instrumentationScope,
        Instrument $instrument,
        int $timestamp,
        iterable $views,
        AttributesFactoryInterface $attributesFactory,
        StalenessHandlerInterface $stalenessHandler,
        MetricSourceRegistryInterface $metricSourceRegistry,
        ?ContextStorageInterface $contextStorage = null
    ): MetricWriterInterface;
}
