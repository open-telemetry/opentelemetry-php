<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics\View;

use OpenTelemetry\SDK\Metrics\Aggregation\ExplicitBucketHistogramAggregation;
use OpenTelemetry\SDK\Metrics\Aggregation\LastValueAggregation;
use OpenTelemetry\SDK\Metrics\Aggregation\SumAggregation;
use OpenTelemetry\SDK\Metrics\AggregationInterface;
use OpenTelemetry\SDK\Metrics\DefaultAggregationProviderInterface;
use OpenTelemetry\SDK\Metrics\Instrument;
use OpenTelemetry\SDK\Metrics\InstrumentType;
use OpenTelemetry\SDK\Metrics\View\ViewTemplate;
use OpenTelemetry\SDK\Metrics\ViewProjection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ViewTemplate::class)]
final class ViewTemplateTest extends TestCase
{
    public function test_empty_template_returns_instrument_defaults(): void
    {
        $this->assertEquals(
            new ViewProjection(
                'name',
                'unit',
                'description',
                null,
                null,
            ),
            ViewTemplate::create()
                ->project(new Instrument(InstrumentType::COUNTER, 'name', 'unit', 'description')),
        );
    }

    public function test_template_returns_assigned_values(): void
    {
        $this->assertEquals(
            new ViewProjection(
                'v-name',
                'unit',
                'v-description',
                ['foo', 'bar'],
                new SumAggregation(),
            ),
            ViewTemplate::create()
                ->withName('v-name')
                ->withDescription('v-description')
                ->withAttributeKeys(['foo', 'bar'])
                ->withAggregation(new SumAggregation())
                ->project(new Instrument(InstrumentType::COUNTER, 'name', 'unit', 'description')),
        );
    }

    public function test_resolver_receives_instrument_type_and_advisory(): void
    {
        $resolver = new class() implements DefaultAggregationProviderInterface {
            public array $calls = [];

            #[\Override]
            public function defaultAggregation($instrumentType, array $advisory = []): AggregationInterface
            {
                $this->calls[] = [$instrumentType, $advisory];

                return new ExplicitBucketHistogramAggregation($advisory['ExplicitBucketBoundaries']);
            }
        };

        $projection = ViewTemplate::create()
            ->withAggregationResolver($resolver)
            ->project(new Instrument(InstrumentType::HISTOGRAM, 'name', 'unit', 'description', ['ExplicitBucketBoundaries' => [1, 2]]));

        $this->assertEquals(new ExplicitBucketHistogramAggregation([1, 2]), $projection->aggregation);
        $this->assertSame([[InstrumentType::HISTOGRAM, ['ExplicitBucketBoundaries' => [1, 2]]]], $resolver->calls);
    }

    public function test_resolver_returning_null_projects_null(): void
    {
        $projection = ViewTemplate::create()
            ->withAggregationResolver(self::resolver(null))
            ->project(new Instrument(InstrumentType::COUNTER, 'name', 'unit', 'description'));

        $this->assertNull($projection->aggregation);
    }

    public function test_aggregation_replaces_resolver(): void
    {
        $projection = ViewTemplate::create()
            ->withAggregationResolver(self::resolver(new LastValueAggregation()))
            ->withAggregation(new SumAggregation())
            ->project(new Instrument(InstrumentType::COUNTER, 'name', 'unit', 'description'));

        $this->assertEquals(new SumAggregation(), $projection->aggregation);
    }

    public function test_resolver_replaces_aggregation(): void
    {
        $projection = ViewTemplate::create()
            ->withAggregation(new SumAggregation())
            ->withAggregationResolver(self::resolver(new LastValueAggregation()))
            ->project(new Instrument(InstrumentType::COUNTER, 'name', 'unit', 'description'));

        $this->assertEquals(new LastValueAggregation(), $projection->aggregation);
    }

    private static function resolver(?AggregationInterface $aggregation): DefaultAggregationProviderInterface
    {
        return new class($aggregation) implements DefaultAggregationProviderInterface {
            public function __construct(private readonly ?AggregationInterface $aggregation)
            {
            }

            #[\Override]
            public function defaultAggregation($instrumentType, array $advisory = []): ?AggregationInterface
            {
                return $this->aggregation;
            }
        };
    }
}
