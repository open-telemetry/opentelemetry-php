<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

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
use function OpenTelemetry\SDK\Common\Util\closure;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilterInterface;
use OpenTelemetry\SDK\Metrics\MetricRegistration\MultiRegistryRegistration;
use OpenTelemetry\SDK\Metrics\MetricRegistration\RegistryRegistration;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use const PHP_VERSION_ID;
use function serialize;

/**
 * @internal
 */
final class Meter implements MeterInterface
{
    private ?ContextStorageInterface $contextStorage;
    private MetricFactoryInterface $metricFactory;
    private ResourceInfo $resource;
    private ClockInterface $clock;
    private AttributesFactoryInterface $attributesFactory;
    private StalenessHandlerFactoryInterface $stalenessHandlerFactory;
    /** @var iterable<MetricSourceRegistryInterface&DefaultAggregationProviderInterface> */
    private iterable $metricRegistries;
    private ViewRegistryInterface $viewRegistry;
    private ?ExemplarFilterInterface $exemplarFilter;
    private MeterInstruments $instruments;
    private InstrumentationScopeInterface $instrumentationScope;

    private ?string $instrumentationScopeId = null;

    /**
     * @param iterable<MetricSourceRegistryInterface&DefaultAggregationProviderInterface> $metricRegistries
     */
    public function __construct(
        ?ContextStorageInterface $contextStorage,
        MetricFactoryInterface $metricFactory,
        ResourceInfo $resource,
        ClockInterface $clock,
        AttributesFactoryInterface $attributesFactory,
        StalenessHandlerFactoryInterface $stalenessHandlerFactory,
        iterable $metricRegistries,
        ViewRegistryInterface $viewRegistry,
        ?ExemplarFilterInterface $exemplarFilter,
        MeterInstruments $instruments,
        InstrumentationScopeInterface $instrumentationScope
    ) {
        $this->contextStorage = $contextStorage;
        $this->metricFactory = $metricFactory;
        $this->resource = $resource;
        $this->clock = $clock;
        $this->attributesFactory = $attributesFactory;
        $this->stalenessHandlerFactory = $stalenessHandlerFactory;
        $this->metricRegistries = $metricRegistries;
        $this->viewRegistry = $viewRegistry;
        $this->exemplarFilter = $exemplarFilter;
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
            /** @psalm-suppress InvalidArgument */
            $observer->observe(closure($callback));
            $referenceCounter->acquire(true);
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
            /** @psalm-suppress InvalidArgument */
            $observer->observe(closure($callback));
            $referenceCounter->acquire(true);
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
            /** @psalm-suppress InvalidArgument */
            $observer->observe(closure($callback));
            $referenceCounter->acquire(true);
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

        $instrumentationScopeId = $this->instrumentationScopeId($this->instrumentationScope);
        $instrumentId = $this->instrumentId($instrument);

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
                $this->viewRegistrationRequests($instrument, $stalenessHandler),
                $this->attributesFactory,
                $this->exemplarFilter,
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

        $instrumentationScopeId = $this->instrumentationScopeId($this->instrumentationScope);
        $instrumentId = $this->instrumentId($instrument);

        $instruments = $this->instruments;
        /** @phan-suppress-next-line PhanDeprecatedProperty */
        $instruments->staleObservers = [];
        if ($observer = $instruments->observers[$instrumentationScopeId][$instrumentId] ?? null) {
            return $observer;
        }

        $stalenessHandler = $this->stalenessHandlerFactory->create();
        $stalenessHandler->onStale(static function () use ($instruments, $instrumentationScopeId, $instrumentId): void {
            if (PHP_VERSION_ID < 80000) {
                /** @phan-suppress-next-line PhanDeprecatedProperty */
                $instruments->staleObservers[] = $instruments->observers[$instrumentationScopeId][$instrumentId][0];
            }

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
                $this->viewRegistrationRequests($instrument, $stalenessHandler),
                $this->attributesFactory,
                $this->exemplarFilter,
            ),
            $stalenessHandler,
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
