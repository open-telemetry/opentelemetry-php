<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics\Exemplar;

use OpenTelemetry\API\Trace\AbstractSpan;
use OpenTelemetry\API\Trace\SpanContextFactory;
use OpenTelemetry\API\Trace\SpanContextInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Metrics\Data\Exemplar;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\AllExemplarFilter;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\NoneExemplarFilter;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\WithSampledTraceExemplarFilter;
use OpenTelemetry\SDK\Metrics\Exemplar\FilteredReservoir;
use OpenTelemetry\SDK\Metrics\Exemplar\FixedSizeReservoir;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Metrics\Exemplar\FilteredReservoir
 */
final class FilteredReservoirTest extends TestCase
{

    /**
     * @covers \OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\AllExemplarFilter
     */
    public function test_all_reservoir_returns_exemplars(): void
    {
        $reservoir = new FilteredReservoir(new FixedSizeReservoir(Attributes::factory(), 4), new AllExemplarFilter());
        $reservoir->offer(0, 5, Attributes::create([]), Context::getRoot(), 7, 0);

        $this->assertEquals([
            0 => [
                new Exemplar(5, 7, Attributes::create([]), null, null),
            ],
        ], $reservoir->collect([0 => Attributes::create([])], 0, 1));
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\NoneExemplarFilter
     */
    public function test_none_reservoir_doesnt_return_exemplars(): void
    {
        $reservoir = new FilteredReservoir(new FixedSizeReservoir(Attributes::factory(), 4), new NoneExemplarFilter());
        $reservoir->offer(0, 5, Attributes::create([]), Context::getRoot(), 7, 0);

        $this->assertEquals([
        ], $reservoir->collect([0 => Attributes::create([])], 0, 1));
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\WithSampledTraceExemplarFilter
     */
    public function test_with_sampled_trace_reservoir_returns_sampled_exemplars(): void
    {
        $reservoir = new FilteredReservoir(new FixedSizeReservoir(Attributes::factory(), 4), new WithSampledTraceExemplarFilter());

        $context = AbstractSpan::wrap(SpanContextFactory::create('12345678901234567890123456789012', '1234567890123456', SpanContextInterface::TRACE_FLAG_SAMPLED))
            ->storeInContext(Context::getRoot());

        $reservoir->offer(0, 5, Attributes::create([]), $context, 7, 0);

        $this->assertEquals([
            0 => [
                new Exemplar(5, 7, Attributes::create([]), '12345678901234567890123456789012', '1234567890123456'),
            ],
        ], $reservoir->collect([0 => Attributes::create([])], 0, 1));
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\WithSampledTraceExemplarFilter
     */
    public function test_with_sampled_trace_reservoir_doesnt_return_not_sampled_exemplars(): void
    {
        $reservoir = new FilteredReservoir(new FixedSizeReservoir(Attributes::factory(), 4), new WithSampledTraceExemplarFilter());

        $context = AbstractSpan::wrap(SpanContextFactory::create('12345678901234567890123456789012', '1234567890123456'))
            ->storeInContext(Context::getRoot());

        $reservoir->offer(0, 5, Attributes::create([]), $context, 7, 0);

        $this->assertEquals([
        ], $reservoir->collect([0 => Attributes::create([])], 0, 1));
    }
}
