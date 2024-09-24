<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use ArrayAccess;
use OpenTelemetry\API\Common\Time\ClockInterface;
use OpenTelemetry\API\Metrics\MeterInterface;
use OpenTelemetry\API\Metrics\Noop\NoopMeter;
use OpenTelemetry\Context\ContextStorageInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesFactoryInterface;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactoryInterface;
use OpenTelemetry\SDK\Common\InstrumentationScope\Configurator;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilterInterface;
use OpenTelemetry\SDK\Metrics\MetricFactory\StreamFactory;
use OpenTelemetry\SDK\Metrics\MetricRegistry\MetricRegistry;
use OpenTelemetry\SDK\Metrics\MetricRegistry\MetricRegistryInterface;
use OpenTelemetry\SDK\Metrics\MetricRegistry\MetricWriterInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Sdk;
use WeakMap;

final class MeterProvider implements MeterProviderInterface
{
    private readonly MeterInstruments $instruments;
    private readonly MetricRegistryInterface $registry;
    private readonly MetricWriterInterface $writer;
    private readonly ArrayAccess $destructors;

    private bool $closed = false;
    private readonly WeakMap $meters;

    /**
     * @param iterable<MetricReaderInterface&MetricSourceRegistryInterface&DefaultAggregationProviderInterface> $metricReaders
     */
    public function __construct(
        ?ContextStorageInterface $contextStorage,
        private readonly ResourceInfo $resource,
        private readonly ClockInterface $clock,
        AttributesFactoryInterface $attributesFactory,
        private readonly InstrumentationScopeFactoryInterface $instrumentationScopeFactory,
        private readonly iterable $metricReaders,
        private readonly ViewRegistryInterface $viewRegistry,
        private readonly ?ExemplarFilterInterface $exemplarFilter,
        private readonly StalenessHandlerFactoryInterface $stalenessHandlerFactory,
        private readonly MetricFactoryInterface $metricFactory = new StreamFactory(),
        private ?Configurator $configurator = null,
    ) {
        $this->instruments = new MeterInstruments();

        $registry = new MetricRegistry($contextStorage, $attributesFactory, $clock);
        $this->registry = $registry;
        $this->writer = $registry;
        $this->destructors = new WeakMap();
        $this->meters = new WeakMap();
    }

    public function getMeter(
        string $name,
        ?string $version = null,
        ?string $schemaUrl = null,
        iterable $attributes = [],
    ): MeterInterface {
        if ($this->closed || Sdk::isDisabled()) { //@todo create meter provider from factory, and move Sdk::isDisabled() there
            return new NoopMeter();
        }

        $meter = new Meter(
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
            $this->configurator,
        );
        $this->meters->offsetSet($meter, null);

        return $meter;
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

    /**
     * Update the {@link Configurator} for a {@link MeterProvider}, which will reconfigure
     *  all meters created from the provider.
     *
     * @experimental
     */
    public function updateConfigurator(Configurator $configurator): void
    {
        $this->configurator = $configurator;

        foreach ($this->meters as $meter => $unused) {
            $meter->updateConfigurator($configurator);
        }
    }
}
