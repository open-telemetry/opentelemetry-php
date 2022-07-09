<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use Closure;
use OpenTelemetry\API\Metrics\CounterInterface;
use OpenTelemetry\API\Metrics\HistogramInterface;
use OpenTelemetry\API\Metrics\MeterInterface;
use OpenTelemetry\API\Metrics\ObservableCounterInterface;
use OpenTelemetry\API\Metrics\ObservableGaugeInterface;
use OpenTelemetry\API\Metrics\ObservableUpDownCounterInterface;
use OpenTelemetry\API\Metrics\UpDownCounterInterface;
use OpenTelemetry\Context\ContextStorageInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesFactoryInterface;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Common\Time\ClockInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use function serialize;

final class Meter implements MeterInterface
{
    private ?ContextStorageInterface $contextStorage;
    private MetricFactoryInterface $metricFactory;
    private ResourceInfo $resource;
    private ClockInterface $clock;
    private AttributesFactoryInterface $attributesFactory;
    private StalenessHandlerFactoryInterface $stalenessHandlerFactory;
    private ViewRegistryInterface $viewRegistry;
    private MetricSourceRegistryInterface $metricSourceRegistry;
    private MeterInstruments $instruments;
    private InstrumentationScopeInterface $instrumentationScope;

    public function __construct(
        ?ContextStorageInterface $contextStorage,
        MetricFactoryInterface $metricFactory,
        ResourceInfo $resource,
        ClockInterface $clock,
        AttributesFactoryInterface $attributesFactory,
        StalenessHandlerFactoryInterface $stalenessHandlerFactory,
        ViewRegistryInterface $viewRegistry,
        MetricSourceRegistryInterface $metricSourceRegistry,
        MeterInstruments $instruments,
        InstrumentationScopeInterface $instrumentationScope
    ) {
        $this->contextStorage = $contextStorage;
        $this->metricFactory = $metricFactory;
        $this->resource = $resource;
        $this->clock = $clock;
        $this->attributesFactory = $attributesFactory;
        $this->stalenessHandlerFactory = $stalenessHandlerFactory;
        $this->viewRegistry = $viewRegistry;
        $this->metricSourceRegistry = $metricSourceRegistry;
        $this->instruments = $instruments;
        $this->instrumentationScope = $instrumentationScope;
    }

    public function createCounter(string $name, ?string $unit = null, ?string $description = null): CounterInterface
    {
        [$writer, $referenceCounter] = $this->createSynchronousWriter(
            InstrumentType::COUNTER,
            $name,
            $unit,
            $description,
        );

        return new Counter($writer, $referenceCounter, $this->clock);
    }

    public function createObservableCounter(string $name, ?string $unit = null, ?string $description = null, callable ...$callbacks): ObservableCounterInterface
    {
        [$observer, $referenceCounter] = $this->createAsynchronousObserver(
            InstrumentType::ASYNCHRONOUS_COUNTER,
            $name,
            $unit,
            $description,
        );

        foreach ($callbacks as $callback) {
            $observer->observe(Closure::fromCallable($callback));
            $referenceCounter->acquire();
        }

        return new ObservableCounter($observer, $referenceCounter);
    }

    public function createHistogram(string $name, ?string $unit = null, ?string $description = null): HistogramInterface
    {
        [$writer, $referenceCounter] = $this->createSynchronousWriter(
            InstrumentType::HISTOGRAM,
            $name,
            $unit,
            $description,
        );

        return new Histogram($writer, $referenceCounter, $this->clock);
    }

    public function createObservableGauge(string $name, ?string $unit = null, ?string $description = null, callable ...$callbacks): ObservableGaugeInterface
    {
        [$observer, $referenceCounter] = $this->createAsynchronousObserver(
            InstrumentType::ASYNCHRONOUS_GAUGE,
            $name,
            $unit,
            $description,
        );

        foreach ($callbacks as $callback) {
            $observer->observe(Closure::fromCallable($callback));
            $referenceCounter->acquire();
        }

        return new ObservableGauge($observer, $referenceCounter);
    }

    public function createUpDownCounter(string $name, ?string $unit = null, ?string $description = null): UpDownCounterInterface
    {
        [$writer, $referenceCounter] = $this->createSynchronousWriter(
            InstrumentType::UP_DOWN_COUNTER,
            $name,
            $unit,
            $description,
        );

        return new UpDownCounter($writer, $referenceCounter, $this->clock);
    }

    public function createObservableUpDownCounter(string $name, ?string $unit = null, ?string $description = null, callable ...$callbacks): ObservableUpDownCounterInterface
    {
        [$observer, $referenceCounter] = $this->createAsynchronousObserver(
            InstrumentType::ASYNCHRONOUS_UP_DOWN_COUNTER,
            $name,
            $unit,
            $description,
        );

        foreach ($callbacks as $callback) {
            $observer->observe(Closure::fromCallable($callback));
            $referenceCounter->acquire();
        }

        return new ObservableUpDownCounter($observer, $referenceCounter);
    }

    /**
     * @param string|InstrumentType $instrumentType
     * @return array{MetricWriterInterface, ReferenceCounterInterface}
     */
    private function createSynchronousWriter($instrumentType, string $name, ?string $unit, ?string $description): array
    {
        $instrument = new Instrument($instrumentType, $name, $unit, $description);

        $instrumentationScopeId = self::instrumentationScopeId($this->instrumentationScope);
        $instrumentId = self::instrumentId($instrument);

        $instruments = $this->instruments;
        if ($writer = $instruments->writers[$instrumentationScopeId][$instrumentId] ?? null) {
            return $writer;
        }

        $stalenessHandler = $this->stalenessHandlerFactory->create();
        $stalenessHandler->onStale(static function () use ($instruments, $instrumentationScopeId, $instrumentId): void {
            unset($instruments->writers[$instrumentationScopeId][$instrumentId]);
            if (!$instruments->writers[$instrumentationScopeId]) {
                unset($instruments->writers[$instrumentationScopeId]);
            }

            $instruments->startTimestamp = null;
        });

        $instruments->startTimestamp ??= $this->clock->now();

        return $instruments->writers[$instrumentationScopeId][$instrumentId] = [
            $this->metricFactory->createSynchronousWriter(
                $this->resource,
                $this->instrumentationScope,
                $instrument,
                $instruments->startTimestamp,
                $this->viewRegistry->find($instrument, $this->instrumentationScope),
                $this->attributesFactory,
                $this->stalenessHandlerFactory->create(),
                $this->metricSourceRegistry,
                $this->contextStorage,
            ),
            $stalenessHandler,
        ];
    }

    /**
     * @param string|InstrumentType $instrumentType
     * @return array{MetricObserverInterface, ReferenceCounterInterface}
     */
    private function createAsynchronousObserver($instrumentType, string $name, ?string $unit, ?string $description): array
    {
        $instrument = new Instrument($instrumentType, $name, $unit, $description);

        $instrumentationScopeId = self::instrumentationScopeId($this->instrumentationScope);
        $instrumentId = self::instrumentId($instrument);

        $instruments = $this->instruments;
        if ($observer = $instruments->observers[$instrumentationScopeId][$instrumentId] ?? null) {
            return $observer;
        }

        $stalenessHandler = $this->stalenessHandlerFactory->create();
        $stalenessHandler->onStale(static function () use ($instruments, $instrumentationScopeId, $instrumentId): void {
            unset($instruments->observers[$instrumentationScopeId][$instrumentId]);
            if (!$instruments->observers[$instrumentationScopeId]) {
                unset($instruments->observers[$instrumentationScopeId]);
            }

            $instruments->startTimestamp = null;
        });

        $instruments->startTimestamp ??= $this->clock->now();

        return $instruments->observers[$instrumentationScopeId][$instrumentId] = [
            $this->metricFactory->createAsynchronousObserver(
                $this->resource,
                $this->instrumentationScope,
                $instrument,
                $instruments->startTimestamp,
                $this->viewRegistry->find($instrument, $this->instrumentationScope),
                $this->attributesFactory,
                $stalenessHandler,
                $this->metricSourceRegistry,
            ),
            $stalenessHandler,
        ];
    }

    private static function instrumentationScopeId(InstrumentationScopeInterface $instrumentationScope): string
    {
        return serialize($instrumentationScope);
    }

    private static function instrumentId(Instrument $instrument): string
    {
        return serialize($instrument);
    }
}
