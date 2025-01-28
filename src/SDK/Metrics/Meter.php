<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use function array_unshift;
use ArrayAccess;
use function assert;
use function is_callable;
use OpenTelemetry\API\Behavior\LogsMessagesTrait;
use OpenTelemetry\API\Common\Time\ClockInterface;
use OpenTelemetry\API\Metrics\AsynchronousInstrument;
use OpenTelemetry\API\Metrics\CounterInterface;
use OpenTelemetry\API\Metrics\GaugeInterface;
use OpenTelemetry\API\Metrics\HistogramInterface;
use OpenTelemetry\API\Metrics\MeterInterface;
use OpenTelemetry\API\Metrics\ObservableCallbackInterface;
use OpenTelemetry\API\Metrics\ObservableCounterInterface;
use OpenTelemetry\API\Metrics\ObservableGaugeInterface;
use OpenTelemetry\API\Metrics\ObservableUpDownCounterInterface;
use OpenTelemetry\API\Metrics\UpDownCounterInterface;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Common\InstrumentationScope\Config;
use OpenTelemetry\SDK\Common\InstrumentationScope\Configurable;
use OpenTelemetry\SDK\Common\InstrumentationScope\Configurator;
use function OpenTelemetry\SDK\Common\Util\closure;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilterInterface;
use OpenTelemetry\SDK\Metrics\MetricRegistration\MultiRegistryRegistration;
use OpenTelemetry\SDK\Metrics\MetricRegistration\RegistryRegistration;
use OpenTelemetry\SDK\Metrics\MetricRegistry\MetricRegistryInterface;
use OpenTelemetry\SDK\Metrics\MetricRegistry\MetricWriterInterface;
use OpenTelemetry\SDK\Metrics\StalenessHandler\MultiReferenceCounter;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use function serialize;

/**
 * @internal
 */
final class Meter implements MeterInterface, Configurable
{
    use LogsMessagesTrait;

    private ?string $instrumentationScopeId = null;
    private Config $config;

    /**
     * @param iterable<MetricSourceRegistryInterface&DefaultAggregationProviderInterface> $metricRegistries
     * @param ArrayAccess<object, ObservableCallbackDestructor> $destructors
     */
    public function __construct(
        private readonly MetricFactoryInterface $metricFactory,
        private readonly ResourceInfo $resource,
        private readonly ClockInterface $clock,
        private readonly StalenessHandlerFactoryInterface $stalenessHandlerFactory,
        private readonly iterable $metricRegistries,
        private readonly ViewRegistryInterface $viewRegistry,
        private readonly ?ExemplarFilterInterface $exemplarFilter,
        private readonly MeterInstruments $instruments,
        private readonly InstrumentationScopeInterface $instrumentationScope,
        private readonly MetricRegistryInterface $registry,
        private readonly MetricWriterInterface $writer,
        private readonly ArrayAccess $destructors,
        ?Configurator $configurator = null,
    ) {
        $this->config = $configurator?->resolve($this->instrumentationScope) ?? MeterConfig::default();
    }

    private static function dummyInstrument(): Instrument
    {
        static $dummy;

        return $dummy ??= (new \ReflectionClass(Instrument::class))->newInstanceWithoutConstructor();
    }

    /**
     * @internal
     */
    public function updateConfigurator(Configurator $configurator): void
    {
        $this->config = $configurator->resolve($this->instrumentationScope);

        $startTimestamp = $this->clock->now();
        foreach ($this->instruments->observers[self::instrumentationScopeId($this->instrumentationScope)] ?? [] as [$instrument, $stalenessHandler, $r]) {
            if ($this->config->isEnabled() && $r->dormant) {
                $this->metricFactory->createAsynchronousObserver(
                    $this->registry,
                    $this->resource,
                    $this->instrumentationScope,
                    $instrument,
                    $startTimestamp,
                    $this->viewRegistrationRequests($instrument, $stalenessHandler),
                );
                $r->dormant = false;
            }
            if (!$this->config->isEnabled() && !$r->dormant) {
                $this->releaseStreams($instrument);
                $r->dormant = true;
            }
        }
        foreach ($this->instruments->writers[self::instrumentationScopeId($this->instrumentationScope)] ?? [] as [$instrument, $stalenessHandler, $r]) {
            if ($this->config->isEnabled() && $r->dormant) {
                $this->metricFactory->createSynchronousWriter(
                    $this->registry,
                    $this->resource,
                    $this->instrumentationScope,
                    $instrument,
                    $startTimestamp,
                    $this->viewRegistrationRequests($instrument, $stalenessHandler),
                    $this->exemplarFilter,
                );
                $r->dormant = false;
            }
            if (!$this->config->isEnabled() && !$r->dormant) {
                $this->releaseStreams($instrument);
                $r->dormant = true;
            }
        }
    }

    public function batchObserve(callable $callback, AsynchronousInstrument $instrument, AsynchronousInstrument ...$instruments): ObservableCallbackInterface
    {
        $referenceCounters = [];
        $handles = [];

        array_unshift($instruments, $instrument);
        foreach ($instruments as $instrument) {
            if (!$instrument instanceof InstrumentHandle) {
                self::logWarning('Ignoring invalid instrument provided to batchObserve, instrument not created by this SDK', ['instrument' => $instrument]);
                $handles[] = self::dummyInstrument();

                continue;
            }

            $asynchronousInstrument = $this->getAsynchronousInstrument($instrument->getHandle(), $this->instrumentationScope);
            if (!$asynchronousInstrument) {
                self::logWarning('Ignoring invalid instrument provided to batchObserve, instrument not created by this meter', ['instrument' => $instrument]);
                $handles[] = self::dummyInstrument();

                continue;
            }

            [
                $handles[],
                $referenceCounters[],
            ] = $asynchronousInstrument;
        }

        assert($handles !== []);

        return AsynchronousInstruments::observe(
            $this->writer,
            $this->destructors,
            $callback,
            $handles,
            new MultiReferenceCounter($referenceCounters),
        );
    }

    public function createCounter(string $name, ?string $unit = null, ?string $description = null, array $advisory = []): CounterInterface
    {
        [$instrument, $referenceCounter] = $this->createSynchronousWriter(
            InstrumentType::COUNTER,
            $name,
            $unit,
            $description,
            $advisory,
        );

        return new Counter($this->writer, $instrument, $referenceCounter);
    }

    public function createObservableCounter(string $name, ?string $unit = null, ?string $description = null, $advisory = [], callable ...$callbacks): ObservableCounterInterface
    {
        if (is_callable($advisory)) {
            array_unshift($callbacks, $advisory);
            $advisory = [];
        }
        [$instrument, $referenceCounter] = $this->createAsynchronousObserver(
            InstrumentType::ASYNCHRONOUS_COUNTER,
            $name,
            $unit,
            $description,
            $advisory,
        );

        foreach ($callbacks as $callback) {
            $this->writer->registerCallback(closure($callback), $instrument);
            $referenceCounter->acquire(true);
        }

        return new ObservableCounter($this->writer, $instrument, $referenceCounter, $this->destructors);
    }

    public function createHistogram(string $name, ?string $unit = null, ?string $description = null, array $advisory = []): HistogramInterface
    {
        [$instrument, $referenceCounter] = $this->createSynchronousWriter(
            InstrumentType::HISTOGRAM,
            $name,
            $unit,
            $description,
            $advisory,
        );

        return new Histogram($this->writer, $instrument, $referenceCounter);
    }

    public function createGauge(string $name, ?string $unit = null, ?string $description = null, array $advisory = []): GaugeInterface
    {
        [$instrument, $referenceCounter] = $this->createSynchronousWriter(
            InstrumentType::GAUGE,
            $name,
            $unit,
            $description,
            $advisory,
        );

        return new Gauge($this->writer, $instrument, $referenceCounter);
    }

    public function createObservableGauge(string $name, ?string $unit = null, ?string $description = null, $advisory = [], callable ...$callbacks): ObservableGaugeInterface
    {
        if (is_callable($advisory)) {
            array_unshift($callbacks, $advisory);
            $advisory = [];
        }
        [$instrument, $referenceCounter] = $this->createAsynchronousObserver(
            InstrumentType::ASYNCHRONOUS_GAUGE,
            $name,
            $unit,
            $description,
            $advisory,
        );

        foreach ($callbacks as $callback) {
            $this->writer->registerCallback(closure($callback), $instrument);
            $referenceCounter->acquire(true);
        }

        return new ObservableGauge($this->writer, $instrument, $referenceCounter, $this->destructors);
    }

    public function createUpDownCounter(string $name, ?string $unit = null, ?string $description = null, array $advisory = []): UpDownCounterInterface
    {
        [$instrument, $referenceCounter] = $this->createSynchronousWriter(
            InstrumentType::UP_DOWN_COUNTER,
            $name,
            $unit,
            $description,
            $advisory,
        );

        return new UpDownCounter($this->writer, $instrument, $referenceCounter);
    }

    public function createObservableUpDownCounter(string $name, ?string $unit = null, ?string $description = null, $advisory = [], callable ...$callbacks): ObservableUpDownCounterInterface
    {
        if (is_callable($advisory)) {
            array_unshift($callbacks, $advisory);
            $advisory = [];
        }
        [$instrument, $referenceCounter] = $this->createAsynchronousObserver(
            InstrumentType::ASYNCHRONOUS_UP_DOWN_COUNTER,
            $name,
            $unit,
            $description,
            $advisory,
        );

        foreach ($callbacks as $callback) {
            $this->writer->registerCallback(closure($callback), $instrument);
            $referenceCounter->acquire(true);
        }

        return new ObservableUpDownCounter($this->writer, $instrument, $referenceCounter, $this->destructors);
    }

    /**
     * @return array{Instrument, ReferenceCounterInterface, RegisteredInstrument}|null
     */
    private function getAsynchronousInstrument(Instrument $instrument, InstrumentationScopeInterface $instrumentationScope): ?array
    {
        $instrumentationScopeId = $this->instrumentationScopeId($instrumentationScope);
        $instrumentId = $this->instrumentId($instrument);

        $asynchronousInstrument = $this->instruments->observers[$instrumentationScopeId][$instrumentId] ?? null;
        if (!$asynchronousInstrument || $asynchronousInstrument[0] !== $instrument) {
            return null;
        }

        return $asynchronousInstrument;
    }

    /**
     * @return array{Instrument, ReferenceCounterInterface, RegisteredInstrument}
     */
    private function createSynchronousWriter(string|InstrumentType $instrumentType, string $name, ?string $unit, ?string $description, array $advisory = []): array
    {
        $instrument = new Instrument($instrumentType, $name, $unit, $description, $advisory);

        $instrumentationScopeId = $this->instrumentationScopeId($this->instrumentationScope);
        $instrumentId = $this->instrumentId($instrument);

        $instruments = $this->instruments;
        if ($writer = $instruments->writers[$instrumentationScopeId][$instrumentId] ?? null) {
            return $writer;
        }

        $stalenessHandler = $this->stalenessHandlerFactory->create();
        if ($this->config->isEnabled()) {
            $instruments->startTimestamp ??= $this->clock->now();
            $this->metricFactory->createSynchronousWriter(
                $this->registry,
                $this->resource,
                $this->instrumentationScope,
                $instrument,
                $instruments->startTimestamp,
                $this->viewRegistrationRequests($instrument, $stalenessHandler),
                $this->exemplarFilter,
            );
        }

        $stalenessHandler->onStale(fn () => $this->releaseStreams($instrument));
        $stalenessHandler->onStale(static function () use ($instruments, $instrumentationScopeId, $instrumentId): void {
            unset($instruments->writers[$instrumentationScopeId][$instrumentId]);
            if (!$instruments->writers[$instrumentationScopeId]) {
                unset($instruments->writers[$instrumentationScopeId]);
            }

            $instruments->startTimestamp = null;
        });

        return $instruments->writers[$instrumentationScopeId][$instrumentId] = [
            $instrument,
            $stalenessHandler,
            new RegisteredInstrument(!$this->config->isEnabled(), $this),
        ];
    }

    /**
     * @return array{Instrument, ReferenceCounterInterface, RegisteredInstrument}
     */
    private function createAsynchronousObserver(string|InstrumentType $instrumentType, string $name, ?string $unit, ?string $description, array $advisory): array
    {
        $instrument = new Instrument($instrumentType, $name, $unit, $description, $advisory);

        $instrumentationScopeId = $this->instrumentationScopeId($this->instrumentationScope);
        $instrumentId = $this->instrumentId($instrument);

        $instruments = $this->instruments;
        if ($observer = $instruments->observers[$instrumentationScopeId][$instrumentId] ?? null) {
            return $observer;
        }

        $stalenessHandler = $this->stalenessHandlerFactory->create();
        if ($this->config->isEnabled()) {
            $instruments->startTimestamp ??= $this->clock->now();
            $this->metricFactory->createAsynchronousObserver(
                $this->registry,
                $this->resource,
                $this->instrumentationScope,
                $instrument,
                $instruments->startTimestamp,
                $this->viewRegistrationRequests($instrument, $stalenessHandler),
            );
        }

        $stalenessHandler->onStale(fn () => $this->releaseStreams($instrument));
        $stalenessHandler->onStale(static function () use ($instruments, $instrumentationScopeId, $instrumentId): void {
            unset($instruments->observers[$instrumentationScopeId][$instrumentId]);
            if (!$instruments->observers[$instrumentationScopeId]) {
                unset($instruments->observers[$instrumentationScopeId]);
            }

            $instruments->startTimestamp = null;
        });

        return $instruments->observers[$instrumentationScopeId][$instrumentId] = [
            $instrument,
            $stalenessHandler,
            new RegisteredInstrument(!$this->config->isEnabled(), $this),
        ];
    }

    private function releaseStreams(Instrument $instrument): void
    {
        foreach ($this->registry->unregisterStreams($instrument) as $streamId) {
            foreach ($this->metricRegistries as $metricRegistry) {
                if ($metricRegistry instanceof MetricSourceRegistryUnregisterInterface) {
                    $metricRegistry->unregisterStream($this->registry, $streamId);
                }
            }
        }
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
                            /** @phan-suppress-next-line PhanParamTooMany @phpstan-ignore-next-line */
                            $metricRegistry->defaultAggregation($instrument->type, $instrument->advisory),
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
        return serialize([
            $instrument->type,
            $instrument->name,
            $instrument->unit,
            $instrument->description,
        ]);
    }
}
