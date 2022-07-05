<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use Closure;
use function in_array;
use OpenTelemetry\API\Metrics\Meter;
use OpenTelemetry\API\Metrics\Noop\NoopMeter;
use OpenTelemetry\ContextStorage;
use OpenTelemetry\SDK\AttributesFactory;
use OpenTelemetry\SDK\Clock;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\InstrumentationScopeFactory;
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
use OpenTelemetry\SDK\Resource;

final class SdkMeterProvider implements MeterProvider
{
    private MetricFactory $metricFactory;
    private AttributesFactory $metricAttributes;
    private Clock $clock;
    private InstrumentationScopeFactory $instrumentationScopeFactory;
    private MetricReader $metricReader;
    private CriteriaViewRegistry $criteriaViewRegistry;

    private bool $closed = false;

    public function __construct(
        ?ContextStorage $contextStorage,
        Resource $resource,
        Clock $clock,
        InstrumentationScopeFactory $instrumentationScopeFactory,
        MetricReader&MetricSourceRegistry $metricReader,
        AttributesFactory $metricAttributes,
        StalenessHandlerFactory $stalenessHandlerFactory,
    ) {
        $this->metricAttributes = $metricAttributes;
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
                    self::defaultAggregation(...),
                    self::defaultExemplarReservoir(...),
                ),
            ]),
            $metricReader,
            $this->metricAttributes,
            $stalenessHandlerFactory,
        ));
    }

    public function getMeter(
        string $name,
        ?string $version = null,
        ?string $schemaUrl = null,
        iterable $attributes = [],
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
        ?Closure $exemplarReservoir = null,
    ): void {
        $this->criteriaViewRegistry->register($criteria, new ViewTemplate(
            $name,
            $description,
            $attributeKeys
                ? new AttributeProcessor\Filtered(
                    $this->metricAttributes,
                    static fn (string $key): bool => in_array($key, $attributeKeys, true),
                )
                : null,
            $aggregation ?? self::defaultAggregation(...),
            $exemplarReservoir ?? self::defaultExemplarReservoir(...),
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

    private static function defaultAggregation(InstrumentType $type): Aggregation
    {
        return match ($type) {
            InstrumentType::Counter,
            InstrumentType::AsynchronousCounter,
                => new Aggregation\Sum(true),
            InstrumentType::UpDownCounter,
            InstrumentType::AsynchronousUpDownCounter,
                => new Aggregation\Sum(),
            InstrumentType::Histogram,
                => new Aggregation\ExplicitBucketHistogram([0, 5, 10, 25, 50, 75, 100, 250, 500, 1000]),
            InstrumentType::AsynchronousGauge,
                => new Aggregation\LastValue(),
        };
    }

    private static function defaultExemplarReservoir(Aggregation $aggregation): ExemplarReservoir
    {
        $reservoir = $aggregation instanceof Aggregation\ExplicitBucketHistogram && $aggregation->boundaries
            ? new HistogramBucketReservoir(Attributes::factory(), $aggregation->boundaries)
            : new FixedSizeReservoir(Attributes::factory(), 5);

        return new FilteredReservoir($reservoir, new ExemplarFilter\WithSampledTrace());
    }
}
