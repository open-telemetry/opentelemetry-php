<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use Closure;
use function in_array;
use LogicException;
use OpenTelemetry\API\Metrics\Meter;
use OpenTelemetry\API\Metrics\Noop\NoopMeter;
use OpenTelemetry\Context\ContextStorageInterface;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Attribute\AttributesFactoryInterface;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactoryInterface;
use OpenTelemetry\SDK\Common\Time\ClockInterface;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarReservoir;
use OpenTelemetry\SDK\Metrics\Exemplar\FilteredReservoir;
use OpenTelemetry\SDK\Metrics\Exemplar\FixedSizeReservoir;
use OpenTelemetry\SDK\Metrics\Exemplar\HistogramBucketReservoir;
use OpenTelemetry\SDK\Metrics\MetricFactory\DeduplicatingFactory;
use OpenTelemetry\SDK\Metrics\MetricFactory\StreamFactory;
use OpenTelemetry\SDK\Metrics\View\CriteriaViewRegistry;
use OpenTelemetry\SDK\Metrics\View\FallbackViewRegistry;
use OpenTelemetry\SDK\Metrics\View\SelectionCriteria;
use OpenTelemetry\SDK\Metrics\View\ViewTemplate;
use OpenTelemetry\SDK\Resource\ResourceInfo;

final class SdkMeterProvider implements MeterProvider
{
    private MetricFactory $metricFactory;
    private AttributesFactoryInterface $attributesFactory;
    private ClockInterface $clock;
    private InstrumentationScopeFactoryInterface $instrumentationScopeFactory;
    private MetricReader $metricReader;
    private CriteriaViewRegistry $criteriaViewRegistry;

    private bool $closed = false;

    /**
     * @param MetricSourceRegistry&MetricReader $metricReader
     */
    public function __construct(
        ?ContextStorageInterface $contextStorage,
        ResourceInfo $resource,
        ClockInterface $clock,
        InstrumentationScopeFactoryInterface $instrumentationScopeFactory,
        $metricReader,
        AttributesFactoryInterface $attributesFactory,
        StalenessHandlerFactory $stalenessHandlerFactory
    ) {
        $this->attributesFactory = $attributesFactory;
        $this->clock = $clock;
        $this->instrumentationScopeFactory = $instrumentationScopeFactory;
        $this->metricReader = $metricReader;
        $this->criteriaViewRegistry = new CriteriaViewRegistry();

        $this->metricFactory = new DeduplicatingFactory(new StreamFactory(
            $contextStorage,
            $resource,
            new FallbackViewRegistry($this->criteriaViewRegistry, [
                new ViewTemplate(
                    null,
                    null,
                    null,
                    self::defaultAggregation(),
                    self::defaultExemplarReservoir(),
                ),
            ]),
            $metricReader,
            $this->attributesFactory,
            $stalenessHandlerFactory,
        ));
    }

    public function getMeter(
        string $name,
        ?string $version = null,
        ?string $schemaUrl = null,
        iterable $attributes = []
    ): Meter {
        if ($this->closed) {
            return new NoopMeter();
        }

        return new SdkMeter(
            $this->metricFactory,
            $this->clock,
            $this->instrumentationScopeFactory->create($name, $version, $schemaUrl, $attributes),
        );
    }

    public function registerView(
        SelectionCriteria $criteria,
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
                ? new AttributeProcessor\Filtered(
                    $this->attributesFactory,
                    static fn (string $key): bool => in_array($key, $attributeKeys, true),
                )
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
     * @return Closure(string|InstrumentType): Aggregation
     */
    private static function defaultAggregation(): Closure
    {
        return static function ($type): Aggregation {
            switch ($type) {
                case InstrumentType::COUNTER:
                case InstrumentType::ASYNCHRONOUS_COUNTER:
                    return new Aggregation\Sum(true);
                case InstrumentType::UP_DOWN_COUNTER:
                case InstrumentType::ASYNCHRONOUS_UP_DOWN_COUNTER:
                    return new Aggregation\Sum();
                case InstrumentType::HISTOGRAM:
                    return new Aggregation\ExplicitBucketHistogram([0, 5, 10, 25, 50, 75, 100, 250, 500, 1000]);
                case InstrumentType::ASYNCHRONOUS_GAUGE:
                    return new Aggregation\LastValue();
            }

            throw new LogicException();
        };
    }

    /**
     * @return Closure(Aggregation, string|InstrumentType): ?ExemplarReservoir
     */
    private static function defaultExemplarReservoir(): Closure
    {
        return static function (Aggregation $aggregation): ExemplarReservoir {
            $reservoir = $aggregation instanceof Aggregation\ExplicitBucketHistogram && $aggregation->boundaries
                ? new HistogramBucketReservoir(Attributes::factory(), $aggregation->boundaries)
                : new FixedSizeReservoir(Attributes::factory(), 5);

            return new FilteredReservoir($reservoir, new ExemplarFilter\WithSampledTrace());
        };
    }
}
