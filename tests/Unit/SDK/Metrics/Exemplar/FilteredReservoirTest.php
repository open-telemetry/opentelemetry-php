<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics\Exemplar;

use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\TraceFlags;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Metrics\Data\Exemplar;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\AllExemplarFilter;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\NoneExemplarFilter;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\WithSampledTraceExemplarFilter;
use OpenTelemetry\SDK\Metrics\Exemplar\FilteredReservoir;
use OpenTelemetry\SDK\Metrics\Exemplar\FixedSizeReservoir;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FilteredReservoir::class)]
#[CoversClass(AllExemplarFilter::class)]
#[CoversClass(NoneExemplarFilter::class)]
#[CoversClass(WithSampledTraceExemplarFilter::class)]
final class FilteredReservoirTest extends TestCase
{

    public function test_all_reservoir_returns_exemplars(): void
    {
        $reservoir = new FilteredReservoir(new FixedSizeReservoir(4), new AllExemplarFilter());
        $reservoir->offer(0, 5, Attributes::create([]), Context::getRoot(), 7);

        $this->assertEquals([
            0 => [
                new Exemplar(0, 5, 7, Attributes::create([]), null, null),
            ],
        ], Exemplar::groupByIndex($reservoir->collect([0 => Attributes::create([])])));
    }

    public function test_none_reservoir_doesnt_return_exemplars(): void
    {
        $reservoir = new FilteredReservoir(new FixedSizeReservoir(4), new NoneExemplarFilter());
        $reservoir->offer(0, 5, Attributes::create([]), Context::getRoot(), 7);

        $this->assertEquals([
        ], Exemplar::groupByIndex($reservoir->collect([0 => Attributes::create([])])));
    }

    public function test_with_sampled_trace_reservoir_returns_sampled_exemplars(): void
    {
        $reservoir = new FilteredReservoir(new FixedSizeReservoir(4), new WithSampledTraceExemplarFilter());

        $context = Span::wrap(SpanContext::create('12345678901234567890123456789012', '1234567890123456', TraceFlags::SAMPLED))
            ->storeInContext(Context::getRoot());

        $reservoir->offer(0, 5, Attributes::create([]), $context, 7);

        $this->assertEquals([
            0 => [
                new Exemplar(0, 5, 7, Attributes::create([]), '12345678901234567890123456789012', '1234567890123456'),
            ],
        ], Exemplar::groupByIndex($reservoir->collect([0 => Attributes::create([])])));
    }

    public function test_with_sampled_trace_reservoir_doesnt_return_not_sampled_exemplars(): void
    {
        $reservoir = new FilteredReservoir(new FixedSizeReservoir(4), new WithSampledTraceExemplarFilter());

        $context = Span::wrap(SpanContext::create('12345678901234567890123456789012', '1234567890123456'))
            ->storeInContext(Context::getRoot());

        $reservoir->offer(0, 5, Attributes::create([]), $context, 7);

        $this->assertEquals([
        ], Exemplar::groupByIndex($reservoir->collect([0 => Attributes::create([])])));
    }
}
