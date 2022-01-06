<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics;

use OpenTelemetry\API\Metrics as API;
use OpenTelemetry\SDK\Metrics\ValueRecorder;
use PHPUnit\Framework\TestCase;
use TypeError;

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
        $this->assertMetrics($this->metric, 1, 5, 5, 5);
        $this->metric->record(2);
        $this->assertMetrics($this->metric, 2, 5, 2, 7);
    }

    public function test_valid_negative_int_record(): void
    {
        $this->metric->record(-5);
        $this->assertMetrics($this->metric, 1, -5, -5, -5);
        $this->metric->record(-2);
        $this->assertMetrics($this->metric, 2, -2, -5, -7);
    }

    public function test_valid_positive_and_negative_int_record(): void
    {
        $this->metric->record(5);
        $this->assertMetrics($this->metric, 1, 5, 5, 5);
        $this->metric->record(-2);
        $this->assertMetrics($this->metric, 2, 5, -2, 3);
    }

    public function test_valid_negative_and_positive_record(): void
    {
        $this->metric->record(-5);
        $this->assertMetrics($this->metric, 1, -5, -5, -5);
        $this->metric->record(2);
        $this->assertMetrics($this->metric, 2, 2, -5, -3);
    }

    public function test_valid_positive_float_record(): void
    {
        $this->metric->record(5.2222);
        $this->assertMetrics($this->metric, 1, 5.2222, 5.2222, 5.2222);
        $this->metric->record(2.6666);
        $this->assertMetrics($this->metric, 2, 5.2222, 2.6666, 7.8888);
    }

    public function test_valid_negative_float_record(): void
    {
        $this->metric->record(-5.2222);
        $this->assertMetrics($this->metric, 1, -5.2222, -5.2222, -5.2222);
        $this->metric->record(-2.6666);
        $this->assertMetrics($this->metric, 2, -2.6666, -5.2222, -7.8888);
    }

    public function test_valid_positive_and_negative_float_record(): void
    {
        $this->metric->record(5.2222);
        $this->assertMetrics($this->metric, 1, 5.2222, 5.2222, 5.2222);
        $this->metric->record(-2.6666);
        $this->assertMetrics($this->metric, 2, 5.2222, -2.6666, 2.5556);
    }

    public function test_valid_negative_and_positive_float_record(): void
    {
        $this->metric->record(-5.2222);
        $this->assertMetrics($this->metric, 1, -5.2222, -5.2222, -5.2222);
        $this->metric->record(2.6666);
        $this->assertMetrics($this->metric, 2, 2.6666, -5.2222, -2.5556);
    }

    private function assertMetrics(ValueRecorder $metric, float $count, float $max, float $min, float $sum): void
    {
        $this->assertEquals($count, $metric->getCount());
        $this->assertEquals($max, $metric->getMax());
        $this->assertEquals($min, $metric->getMin());
        $this->assertEquals($sum, $metric->getSum());
    }

    public function test_value_recorder_initialization(): void
    {
        $this->assertEquals(0, $this->metric->getCount());
        $this->assertEquals(INF, $this->metric->getMax());
        $this->assertEquals(-INF, $this->metric->getMin());
        $this->assertEquals(0, $this->metric->getSum());
        $this->assertEquals(0, $this->metric->getMean());
    }

    public function test_invalid_value_recorder_record_throws_type_error(): void
    {
        $this->expectException(TypeError::class);
        /**
         * @phpstan-ignore-next-line
         * @psalm-suppress InvalidArgument
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
