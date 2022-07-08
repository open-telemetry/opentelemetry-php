<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use Closure;
use LogicException;
use OpenTelemetry\API\Metrics\MeterInterface;
use OpenTelemetry\API\Metrics\Noop\NoopMeter;
use OpenTelemetry\Context\ContextStorageInterface;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Attribute\AttributesFactoryInterface;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactoryInterface;
use OpenTelemetry\SDK\Common\Time\ClockInterface;
use OpenTelemetry\SDK\Metrics\AttributeProcessor\FilteredAttributeProcessor;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\WithSampledTraceExemplarFilter;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarReservoirInterface;
use OpenTelemetry\SDK\Metrics\Exemplar\FilteredReservoir;
use OpenTelemetry\SDK\Metrics\Exemplar\FixedSizeReservoir;
use OpenTelemetry\SDK\Metrics\Exemplar\HistogramBucketReservoir;
use OpenTelemetry\SDK\Metrics\MetricFactory\StreamFactory;
use OpenTelemetry\SDK\Metrics\View\CriteriaViewRegistry;
use OpenTelemetry\SDK\Metrics\View\FallbackViewRegistry;
use OpenTelemetry\SDK\Metrics\View\SelectionCriteriaInterface;
use OpenTelemetry\SDK\Metrics\View\ViewTemplate;
use OpenTelemetry\SDK\Resource\ResourceInfo;

final class MeterProvider implements MeterProviderInterface
{
    private MetricFactoryInterface $metricFactory;
    private AttributesFactoryInterface $attributesFactory;
    private ClockInterface $clock;
    private InstrumentationScopeFactoryInterface $instrumentationScopeFactory;
    private MetricReaderInterface $metricReader;
    private StalenessHandlerFactoryInterface $stalenessHandlerFactory;
    private CriteriaViewRegistry $criteriaViewRegistry;
    private ViewRegistryInterface $viewRegistry;
    private MeterInstruments $instruments;

    private bool $closed = false;

    /**
     * @param MetricSourceRegistryInterface&MetricReaderInterface $metricReader
     */
    public function __construct(
        ?ContextStorageInterface $contextStorage,
        ResourceInfo $resource,
        ClockInterface $clock,
        InstrumentationScopeFactoryInterface $instrumentationScopeFactory,
        $metricReader,
        AttributesFactoryInterface $attributesFactory,
        StalenessHandlerFactoryInterface $stalenessHandlerFactory
    ) {
        $this->metricFactory = new StreamFactory(
            $contextStorage,
            $resource,
            $metricReader,
            $attributesFactory,
        );
        $this->attributesFactory = $attributesFactory;
        $this->clock = $clock;
        $this->instrumentationScopeFactory = $instrumentationScopeFactory;
        $this->metricReader = $metricReader;
        $this->stalenessHandlerFactory = $stalenessHandlerFactory;
        $this->criteriaViewRegistry = new CriteriaViewRegistry();
        $this->viewRegistry = new FallbackViewRegistry($this->criteriaViewRegistry, [
            new ViewTemplate(
                null,
                null,
                null,
                self::defaultAggregation(),
                self::defaultExemplarReservoir(),
            ),
        ]);
        $this->instruments = new MeterInstruments();
    }

    public function getMeter(
        string $name,
        ?string $version = null,
        ?string $schemaUrl = null,
        iterable $attributes = []
    ): MeterInterface {
        if ($this->closed) {
            return new NoopMeter();
        }

        return new Meter(
            $this->metricFactory,
            $this->clock,
            $this->stalenessHandlerFactory,
            $this->viewRegistry,
            $this->instruments,
            $this->instrumentationScopeFactory->create($name, $version, $schemaUrl, $attributes),
        );
    }

    public function registerView(
        SelectionCriteriaInterface $criteria,
        ?string $name = null,
        ?string $description = null,
        ?array $attributeKeys = null,
        ?Closure $aggregation = null,
        ?Closure $exemplarReservoir = null
    ): void {
        $this->criteriaViewRegistry->register($criteria, new ViewTemplate(
            $name,
            $description,
            $attributeKeys
                ? new FilteredAttributeProcessor($this->attributesFactory, $attributeKeys)
                : null,
            $aggregation ?? self::defaultAggregation(),
            $exemplarReservoir ?? self::defaultExemplarReservoir(),
        ));
    }

    public function shutdown(): bool
    {
        if ($this->closed) {
            return false;
        }

        $this->closed = true;

        return $this->metricReader->shutdown();
    }

    public function forceFlush(): bool
    {
        if ($this->closed) {
            return false;
        }

        return $this->metricReader->forceFlush();
    }

    /**
     * @return Closure(string|InstrumentType): AggregationInterface
     */
    private static function defaultAggregation(): Closure
    {
        return static function ($type): AggregationInterface {
            switch ($type) {
                case InstrumentType::COUNTER:
                case InstrumentType::ASYNCHRONOUS_COUNTER:
                    return new Aggregation\SumAggregation(true);
                case InstrumentType::UP_DOWN_COUNTER:
                case InstrumentType::ASYNCHRONOUS_UP_DOWN_COUNTER:
                    return new Aggregation\SumAggregation();
                case InstrumentType::HISTOGRAM:
                    return new Aggregation\ExplicitBucketHistogramAggregation([0, 5, 10, 25, 50, 75, 100, 250, 500, 1000]);
                case InstrumentType::ASYNCHRONOUS_GAUGE:
                    return new Aggregation\LastValueAggregation();
            }

            throw new LogicException();
        };
    }

    /**
     * @return Closure(AggregationInterface, string|InstrumentType): ?ExemplarReservoirInterface
     */
    private static function defaultExemplarReservoir(): Closure
    {
        return static function (AggregationInterface $aggregation): ExemplarReservoirInterface {
            $reservoir = $aggregation instanceof Aggregation\ExplicitBucketHistogramAggregation && $aggregation->boundaries
                ? new HistogramBucketReservoir(Attributes::factory(), $aggregation->boundaries)
                : new FixedSizeReservoir(Attributes::factory(), 5);

            return new FilteredReservoir($reservoir, new WithSampledTraceExemplarFilter());
        };
    }
}
