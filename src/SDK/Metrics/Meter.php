<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use ArrayAccess;
use OpenTelemetry\API\Metrics\CounterInterface;
use OpenTelemetry\API\Metrics\HistogramInterface;
use OpenTelemetry\API\Metrics\MeterInterface;
use OpenTelemetry\API\Metrics\ObservableCounterInterface;
use OpenTelemetry\API\Metrics\ObservableGaugeInterface;
use OpenTelemetry\API\Metrics\ObservableUpDownCounterInterface;
use OpenTelemetry\API\Metrics\UpDownCounterInterface;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Common\Time\ClockInterface;
use function OpenTelemetry\SDK\Common\Util\closure;
use OpenTelemetry\SDK\Common\Util\WeakMap;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilterInterface;
use OpenTelemetry\SDK\Metrics\MetricRegistration\MultiRegistryRegistration;
use OpenTelemetry\SDK\Metrics\MetricRegistration\RegistryRegistration;
use OpenTelemetry\SDK\Metrics\MetricRegistry\MetricRegistryInterface;
use OpenTelemetry\SDK\Metrics\MetricRegistry\MetricWriterInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use const PHP_VERSION_ID;
use function serialize;

/**
 * @internal
 */
final class Meter implements MeterInterface
{
    private MetricFactoryInterface $metricFactory;
    private ResourceInfo $resource;
    private ClockInterface $clock;
    private StalenessHandlerFactoryInterface $stalenessHandlerFactory;
    /** @var iterable<MetricSourceRegistryInterface&DefaultAggregationProviderInterface> */
    private iterable $metricRegistries;
    private ViewRegistryInterface $viewRegistry;
    private ?ExemplarFilterInterface $exemplarFilter;
    private MeterInstruments $instruments;
    private InstrumentationScopeInterface $instrumentationScope;

    private MetricRegistryInterface $registry;
    private MetricWriterInterface $writer;

    private ?string $instrumentationScopeId = null;

    /**
     * @param iterable<MetricSourceRegistryInterface&DefaultAggregationProviderInterface> $metricRegistries
     */
    public function __construct(
        MetricFactoryInterface $metricFactory,
        ResourceInfo $resource,
        ClockInterface $clock,
        StalenessHandlerFactoryInterface $stalenessHandlerFactory,
        iterable $metricRegistries,
        ViewRegistryInterface $viewRegistry,
        ?ExemplarFilterInterface $exemplarFilter,
        MeterInstruments $instruments,
        InstrumentationScopeInterface $instrumentationScope,
        MetricRegistryInterface $registry,
        MetricWriterInterface $writer
    ) {
        $this->metricFactory = $metricFactory;
        $this->resource = $resource;
        $this->clock = $clock;
        $this->stalenessHandlerFactory = $stalenessHandlerFactory;
        $this->metricRegistries = $metricRegistries;
        $this->viewRegistry = $viewRegistry;
        $this->exemplarFilter = $exemplarFilter;
        $this->instruments = $instruments;
        $this->instrumentationScope = $instrumentationScope;
        $this->registry = $registry;
        $this->writer = $writer;
    }

    public function createCounter(string $name, ?string $unit = null, ?string $description = null): CounterInterface
    {
        [$instrument, $referenceCounter] = $this->createSynchronousWriter(
            InstrumentType::COUNTER,
            $name,
            $unit,
            $description,
        );

        return new Counter($this->writer, $instrument, $referenceCounter);
    }

    public function createObservableCounter(string $name, ?string $unit = null, ?string $description = null, callable ...$callbacks): ObservableCounterInterface
    {
        [$instrument, $referenceCounter, $destructors] = $this->createAsynchronousObserver(
            InstrumentType::ASYNCHRONOUS_COUNTER,
            $name,
            $unit,
            $description,
        );

        foreach ($callbacks as $callback) {
            $this->writer->registerCallback(closure($callback), $instrument);
            $referenceCounter->acquire(true);
        }

        return new ObservableCounter($this->writer, $instrument, $referenceCounter, $destructors);
    }

    public function createHistogram(string $name, ?string $unit = null, ?string $description = null): HistogramInterface
    {
        [$instrument, $referenceCounter] = $this->createSynchronousWriter(
            InstrumentType::HISTOGRAM,
            $name,
            $unit,
            $description,
        );

        return new Histogram($this->writer, $instrument, $referenceCounter);
    }

    public function createObservableGauge(string $name, ?string $unit = null, ?string $description = null, callable ...$callbacks): ObservableGaugeInterface
    {
        [$instrument, $referenceCounter, $destructors] = $this->createAsynchronousObserver(
            InstrumentType::ASYNCHRONOUS_GAUGE,
            $name,
            $unit,
            $description,
        );

        foreach ($callbacks as $callback) {
            $this->writer->registerCallback(closure($callback), $instrument);
            $referenceCounter->acquire(true);
        }

        return new ObservableGauge($this->writer, $instrument, $referenceCounter, $destructors);
    }

    public function createUpDownCounter(string $name, ?string $unit = null, ?string $description = null): UpDownCounterInterface
    {
        [$instrument, $referenceCounter] = $this->createSynchronousWriter(
            InstrumentType::UP_DOWN_COUNTER,
            $name,
            $unit,
            $description,
        );

        return new UpDownCounter($this->writer, $instrument, $referenceCounter);
    }

    public function createObservableUpDownCounter(string $name, ?string $unit = null, ?string $description = null, callable ...$callbacks): ObservableUpDownCounterInterface
    {
        [$instrument, $referenceCounter, $destructors] = $this->createAsynchronousObserver(
            InstrumentType::ASYNCHRONOUS_UP_DOWN_COUNTER,
            $name,
            $unit,
            $description,
        );

        foreach ($callbacks as $callback) {
            $this->writer->registerCallback(closure($callback), $instrument);
            $referenceCounter->acquire(true);
        }

        return new ObservableUpDownCounter($this->writer, $instrument, $referenceCounter, $destructors);
    }

    /**
     * @param string|InstrumentType $instrumentType
     * @return array{Instrument, ReferenceCounterInterface}
     */
    private function createSynchronousWriter($instrumentType, string $name, ?string $unit, ?string $description): array
    {
        $instrument = new Instrument($instrumentType, $name, $unit, $description);

        $instrumentationScopeId = $this->instrumentationScopeId($this->instrumentationScope);
        $instrumentId = $this->instrumentId($instrument);

        $instruments = $this->instruments;
        if ($writer = $instruments->writers[$instrumentationScopeId][$instrumentId] ?? null) {
            return $writer;
        }

        $stalenessHandler = $this->stalenessHandlerFactory->create();
        $instruments->startTimestamp ??= $this->clock->now();
        $streamIds = $this->metricFactory->createSynchronousWriter(
            $this->registry,
            $this->resource,
            $this->instrumentationScope,
            $instrument,
            $instruments->startTimestamp,
            $this->viewRegistrationRequests($instrument, $stalenessHandler),
            $this->exemplarFilter,
        );

        $registry = $this->registry;
        $stalenessHandler->onStale(static function () use ($instruments, $instrumentationScopeId, $instrumentId, $registry, $streamIds): void {
            unset($instruments->writers[$instrumentationScopeId][$instrumentId]);
            if (!$instruments->writers[$instrumentationScopeId]) {
                unset($instruments->writers[$instrumentationScopeId]);
            }
            foreach ($streamIds as $streamId) {
                $registry->unregisterStream($streamId);
            }

            $instruments->startTimestamp = null;
        });

        return $instruments->writers[$instrumentationScopeId][$instrumentId] = [
            $instrument,
            $stalenessHandler,
        ];
    }

    /**
     * @param string|InstrumentType $instrumentType
     * @return array{Instrument, ReferenceCounterInterface, ArrayAccess<object, ObservableCallbackDestructor>}
     */
    private function createAsynchronousObserver($instrumentType, string $name, ?string $unit, ?string $description): array
    {
        $instrument = new Instrument($instrumentType, $name, $unit, $description);

        $instrumentationScopeId = $this->instrumentationScopeId($this->instrumentationScope);
        $instrumentId = $this->instrumentId($instrument);

        $instruments = $this->instruments;
        /** @phan-suppress-next-line PhanDeprecatedProperty */
        $instruments->staleObservers = [];
        if ($observer = $instruments->observers[$instrumentationScopeId][$instrumentId] ?? null) {
            return $observer;
        }

        $stalenessHandler = $this->stalenessHandlerFactory->create();
        $instruments->startTimestamp ??= $this->clock->now();
        $streamIds = $this->metricFactory->createAsynchronousObserver(
            $this->registry,
            $this->resource,
            $this->instrumentationScope,
            $instrument,
            $instruments->startTimestamp,
            $this->viewRegistrationRequests($instrument, $stalenessHandler),
        );

        $registry = $this->registry;
        $stalenessHandler->onStale(static function () use ($instruments, $instrumentationScopeId, $instrumentId, $registry, $streamIds): void {
            if (PHP_VERSION_ID < 80000) {
                /** @phan-suppress-next-line PhanDeprecatedProperty */
                $instruments->staleObservers[] = $instruments->observers[$instrumentationScopeId][$instrumentId][2];
            }

            unset($instruments->observers[$instrumentationScopeId][$instrumentId]);
            if (!$instruments->observers[$instrumentationScopeId]) {
                unset($instruments->observers[$instrumentationScopeId]);
            }
            foreach ($streamIds as $streamId) {
                $registry->unregisterStream($streamId);
            }

            $instruments->startTimestamp = null;
        });

        /** @var ArrayAccess<object, ObservableCallbackDestructor> $destructors */
        $destructors = WeakMap::create();

        return $instruments->observers[$instrumentationScopeId][$instrumentId] = [
            $instrument,
            $stalenessHandler,
            $destructors,
        ];
    }

    /**
     * @return iterable<array{ViewProjection, MetricRegistrationInterface}>
     */
    private function viewRegistrationRequests(Instrument $instrument, StalenessHandlerInterface $stalenessHandler): iterable
    {
        $views = $this->viewRegistry->find($instrument, $this->instrumentationScope) ?? [
            new ViewProjection(
                $instrument->name,
                $instrument->unit,
                $instrument->description,
                null,
                null,
            ),
        ];

        $compositeRegistration = new MultiRegistryRegistration($this->metricRegistries, $stalenessHandler);
        foreach ($views as $view) {
            if ($view->aggregation !== null) {
                yield [$view, $compositeRegistration];
            } else {
                foreach ($this->metricRegistries as $metricRegistry) {
                    yield [
                        new ViewProjection(
                            $view->name,
                            $view->unit,
                            $view->description,
                            $view->attributeKeys,
                            $metricRegistry->defaultAggregation($instrument->type),
                        ),
                        new RegistryRegistration($metricRegistry, $stalenessHandler),
                    ];
                }
            }
        }
    }

    private function instrumentationScopeId(InstrumentationScopeInterface $instrumentationScope): string
    {
        return $this->instrumentationScopeId ??= serialize($instrumentationScope);
    }

    private function instrumentId(Instrument $instrument): string
    {
        return serialize($instrument);
    }
}
