<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use ArrayAccess;
use OpenTelemetry\API\Metrics\MeterInterface;
use OpenTelemetry\API\Metrics\Noop\NoopMeter;
use OpenTelemetry\Context\ContextStorageInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesFactoryInterface;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactoryInterface;
use OpenTelemetry\SDK\Common\Time\ClockInterface;
use OpenTelemetry\SDK\Common\Util\WeakMap;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilterInterface;
use OpenTelemetry\SDK\Metrics\MetricFactory\StreamFactory;
use OpenTelemetry\SDK\Metrics\MetricRegistry\MetricRegistry;
use OpenTelemetry\SDK\Metrics\MetricRegistry\MetricRegistryInterface;
use OpenTelemetry\SDK\Metrics\MetricRegistry\MetricWriterInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Sdk;

final class MeterProvider implements MeterProviderInterface
{
    private MetricFactoryInterface $metricFactory;
    private ResourceInfo $resource;
    private ClockInterface $clock;
    private InstrumentationScopeFactoryInterface $instrumentationScopeFactory;
    private iterable $metricReaders;
    private ViewRegistryInterface $viewRegistry;
    private ?ExemplarFilterInterface $exemplarFilter;
    private StalenessHandlerFactoryInterface $stalenessHandlerFactory;
    private MeterInstruments $instruments;
    private MetricRegistryInterface $registry;
    private MetricWriterInterface $writer;
    private ArrayAccess $destructors;

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
        $this->metricFactory = $metricFactory ?? new StreamFactory();
        $this->resource = $resource;
        $this->clock = $clock;
        $this->instrumentationScopeFactory = $instrumentationScopeFactory;
        $this->metricReaders = $metricReaders;
        $this->viewRegistry = $viewRegistry;
        $this->exemplarFilter = $exemplarFilter;
        $this->stalenessHandlerFactory = $stalenessHandlerFactory;
        $this->instruments = new MeterInstruments();

        $registry = new MetricRegistry($contextStorage, $attributesFactory, $clock);
        $this->registry = $registry;
        $this->writer = $registry;
        $this->destructors = WeakMap::create();
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
            $this->metricFactory,
            $this->resource,
            $this->clock,
            $this->stalenessHandlerFactory,
            $this->metricReaders,
            $this->viewRegistry,
            $this->exemplarFilter,
            $this->instruments,
            $this->instrumentationScopeFactory->create($name, $version, $schemaUrl, $attributes),
            $this->registry,
            $this->writer,
            $this->destructors,
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
