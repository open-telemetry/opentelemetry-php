<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics;

use InvalidArgumentException;
use OpenTelemetry\API\Metrics as API;
use OpenTelemetry\SDK\Metrics\ValueRecorder;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\SDK\Metrics\ValueRecorder
 */
class ValueRecorderTest extends TestCase
{
    private ValueRecorder $metric;

    public function setUp(): void
    {
        $this->metric = new ValueRecorder('name', 'description');
    }

    public function test_get_type(): void
    {
        $this->assertSame(API\MetricKind::VALUE_RECORDER, $this->metric->getType());
    }

    public function test_valid_positive_int_record(): void
    {
        $this->metric->record(5);
        $this->assertEquals(1, $this->metric->getCount());
        $this->assertEquals(5, $this->metric->getMax());
        $this->assertEquals(5, $this->metric->getMin());
        $this->assertEquals(5, $this->metric->getSum());
        $this->metric->record(2);
        $this->assertEquals(2, $this->metric->getCount());
        $this->assertEquals(5, $this->metric->getMax());
        $this->assertEquals(2, $this->metric->getMin());
        $this->assertEquals(7, $this->metric->getSum());
    }

    public function test_valid_negative_int_record(): void
    {
        $this->metric->record(-5);
        $this->assertEquals(1, $this->metric->getCount());
        $this->assertEquals(-5, $this->metric->getMax());
        $this->assertEquals(-5, $this->metric->getMin());
        $this->assertEquals(-5, $this->metric->getSum());
        $this->metric->record(-2);
        $this->assertEquals(2, $this->metric->getCount());
        $this->assertEquals(-2, $this->metric->getMax());
        $this->assertEquals(-5, $this->metric->getMin());
        $this->assertEquals(-7, $this->metric->getSum());
    }

    public function test_valid_positive_and_negative_int_record(): void
    {
        $this->metric->record(5);
        $this->assertEquals(1, $this->metric->getCount());
        $this->assertEquals(5, $this->metric->getMax());
        $this->assertEquals(5, $this->metric->getMin());
        $this->assertEquals(5, $this->metric->getSum());
        $this->metric->record(-2);
        $this->assertEquals(2, $this->metric->getCount());
        $this->assertEquals(5, $this->metric->getMax());
        $this->assertEquals(-2, $this->metric->getMin());
        $this->assertEquals(3, $this->metric->getSum());
    }

    public function test_valid_negative_and_positive_record(): void
    {
        $this->metric->record(-5);
        $this->assertEquals(1, $this->metric->getCount());
        $this->assertEquals(-5, $this->metric->getMax());
        $this->assertEquals(-5, $this->metric->getMin());
        $this->assertEquals(-5, $this->metric->getSum());
        $this->metric->record(2);
        $this->assertEquals(2, $this->metric->getCount());
        $this->assertEquals(2, $this->metric->getMax());
        $this->assertEquals(-5, $this->metric->getMin());
        $this->assertEquals(-3, $this->metric->getSum());
    }

    public function test_valid_positive_float_record(): void
    {
        $this->metric->record(5.2222);
        $this->assertEquals(1, $this->metric->getCount());
        $this->assertEquals(5.2222, $this->metric->getMax());
        $this->assertEquals(5.2222, $this->metric->getMin());
        $this->assertEquals(5.2222, $this->metric->getSum());
        $this->metric->record(2.6666);
        $this->assertEquals(2, $this->metric->getCount());
        $this->assertEquals(5.2222, $this->metric->getMax());
        $this->assertEquals(2.6666, $this->metric->getMin());
        $this->assertEquals(7.8888, $this->metric->getSum());
    }

    public function test_valid_negative_float_record(): void
    {
        $this->metric->record(-5.2222);
        $this->assertEquals(1, $this->metric->getCount());
        $this->assertEquals(-5.2222, $this->metric->getMax());
        $this->assertEquals(-5.2222, $this->metric->getMin());
        $this->assertEquals(-5.2222, $this->metric->getSum());
        $this->metric->record(-2.6666);
        $this->assertEquals(2, $this->metric->getCount());
        $this->assertEquals(-2.6666, $this->metric->getMax());
        $this->assertEquals(-5.2222, $this->metric->getMin());
        $this->assertEquals(-7.8888, $this->metric->getSum());
    }

    public function test_valid_positive_and_negative_float_record(): void
    {
        $this->metric->record(5.2222);
        $this->assertEquals(1, $this->metric->getCount());
        $this->assertEquals(5.2222, $this->metric->getMax());
        $this->assertEquals(5.2222, $this->metric->getMin());
        $this->assertEquals(5.2222, $this->metric->getSum());
        $this->metric->record(-2.6666);
        $this->assertEquals(2, $this->metric->getCount());
        $this->assertEquals(5.2222, $this->metric->getMax());
        $this->assertEquals(-2.6666, $this->metric->getMin());
        $this->assertEquals(2.5556, $this->metric->getSum());
    }

    public function test_valid_negative_and_positive_float_record(): void
    {
        $this->metric->record(-5.2222);
        $this->assertEquals(1, $this->metric->getCount());
        $this->assertEquals(-5.2222, $this->metric->getMax());
        $this->assertEquals(-5.2222, $this->metric->getMin());
        $this->assertEquals(-5.2222, $this->metric->getSum());
        $this->metric->record(2.6666);
        $this->assertEquals(2, $this->metric->getCount());
        $this->assertEquals(2.6666, $this->metric->getMax());
        $this->assertEquals(-5.2222, $this->metric->getMin());
        $this->assertEquals(-2.5556, $this->metric->getSum());
    }

    public function test_value_recorder_initialization(): void
    {
        $this->assertEquals(0, $this->metric->getCount());
        $this->assertEquals(INF, $this->metric->getMax());
        $this->assertEquals(-INF, $this->metric->getMin());
        $this->assertEquals(0, $this->metric->getSum());
        $this->assertEquals(0, $this->metric->getMean());
    }

    public function test_invalid_value_recorder_record_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        /**
         * @phpstan-ignore-next-line
         * @psalm-suppress InvalidScalarArgument
         */
        $this->metric->record('a');
    }

    public function test_get_mean(): void
    {
        $this->metric->record(2);
        $this->metric->record(5);
        $this->assertSame(3.5, $this->metric->getMean());
    }
}
