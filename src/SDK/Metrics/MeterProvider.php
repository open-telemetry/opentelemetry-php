<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\API\Metrics\MeterInterface;
use OpenTelemetry\API\Metrics\Noop\NoopMeter;
use OpenTelemetry\Context\ContextStorageInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesFactoryInterface;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactoryInterface;
use OpenTelemetry\SDK\Common\Time\ClockInterface;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilterInterface;
use OpenTelemetry\SDK\Metrics\MetricFactory\StreamFactory;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Sdk;

final class MeterProvider implements MeterProviderInterface
{
    private ?ContextStorageInterface $contextStorage;
    private MetricFactoryInterface $metricFactory;
    private ResourceInfo $resource;
    private ClockInterface $clock;
    private AttributesFactoryInterface $attributesFactory;
    private InstrumentationScopeFactoryInterface $instrumentationScopeFactory;
    private iterable $metricReaders;
    private ViewRegistryInterface $viewRegistry;
    private ?ExemplarFilterInterface $exemplarFilter;
    private StalenessHandlerFactoryInterface $stalenessHandlerFactory;
    private MeterInstruments $instruments;

    private bool $closed = false;

    /**
     * @param iterable<MetricReaderInterface&MetricSourceRegistryInterface&DefaultAggregationProviderInterface> $metricReaders
     */
    public function __construct(
        ?ContextStorageInterface $contextStorage,
        ResourceInfo $resource,
        ClockInterface $clock,
        AttributesFactoryInterface $attributesFactory,
        InstrumentationScopeFactoryInterface $instrumentationScopeFactory,
        iterable $metricReaders,
        ViewRegistryInterface $viewRegistry,
        ?ExemplarFilterInterface $exemplarFilter,
        StalenessHandlerFactoryInterface $stalenessHandlerFactory,
        MetricFactoryInterface $metricFactory = null
    ) {
        $this->contextStorage = $contextStorage;
        $this->metricFactory = $metricFactory ?? new StreamFactory();
        $this->resource = $resource;
        $this->clock = $clock;
        $this->attributesFactory = $attributesFactory;
        $this->instrumentationScopeFactory = $instrumentationScopeFactory;
        $this->metricReaders = $metricReaders;
        $this->viewRegistry = $viewRegistry;
        $this->exemplarFilter = $exemplarFilter;
        $this->stalenessHandlerFactory = $stalenessHandlerFactory;
        $this->instruments = new MeterInstruments();
    }

    public function getMeter(
        string $name,
        ?string $version = null,
        ?string $schemaUrl = null,
        iterable $attributes = []
    ): MeterInterface {
        if ($this->closed || Sdk::isDisabled()) { //@todo create meter provider from factory, and move Sdk::isDisabled() there
            return new NoopMeter();
        }

        return new Meter(
            $this->contextStorage,
            $this->metricFactory,
            $this->resource,
            $this->clock,
            $this->attributesFactory,
            $this->stalenessHandlerFactory,
            $this->metricReaders,
            $this->viewRegistry,
            $this->exemplarFilter,
            $this->instruments,
            $this->instrumentationScopeFactory->create($name, $version, $schemaUrl, $attributes),
        );
    }

    public function shutdown(): bool
    {
        if ($this->closed) {
            return false;
        }

        $this->closed = true;

        $success = true;
        foreach ($this->metricReaders as $metricReader) {
            if (!$metricReader->shutdown()) {
                $success = false;
            }
        }

        return $success;
    }

    public function forceFlush(): bool
    {
        if ($this->closed) {
            return false;
        }

        $success = true;
        foreach ($this->metricReaders as $metricReader) {
            if (!$metricReader->forceFlush()) {
                $success = false;
            }
        }

        return $success;
    }

    public static function builder(): MeterProviderBuilder
    {
        return new MeterProviderBuilder();
    }
}
