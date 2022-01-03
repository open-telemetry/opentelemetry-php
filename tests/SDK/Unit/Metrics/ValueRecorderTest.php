<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Metrics;

use InvalidArgumentException;
use OpenTelemetry\SDK\Metrics\ValueRecorder;
use PHPUnit\Framework\TestCase;

class ValueRecorderTest extends TestCase
{
    /**
     * @test
     */
    public function test_valid_positive_int_record(): void
    {
        $metric = new ValueRecorder('name', 'description');
        $metric->record(5);
        $this->assertEquals(1, $metric->getCount());
        $this->assertEquals(5, $metric->getMax());
        $this->assertEquals(5, $metric->getMin());
        $this->assertEquals(5, $metric->getSum());
        $metric->record(2);
        $this->assertEquals(2, $metric->getCount());
        $this->assertEquals(5, $metric->getMax());
        $this->assertEquals(2, $metric->getMin());
        $this->assertEquals(7, $metric->getSum());
    }

    public function test_valid_negative_int_record(): void
    {
        $metric = new ValueRecorder('name', 'description');
        $metric->record(-5);
        $this->assertEquals(1, $metric->getCount());
        $this->assertEquals(-5, $metric->getMax());
        $this->assertEquals(-5, $metric->getMin());
        $this->assertEquals(-5, $metric->getSum());
        $metric->record(-2);
        $this->assertEquals(2, $metric->getCount());
        $this->assertEquals(-2, $metric->getMax());
        $this->assertEquals(-5, $metric->getMin());
        $this->assertEquals(-7, $metric->getSum());
    }

    public function test_valid_positive_and_negative_int_record(): void
    {
        $metric = new ValueRecorder('name', 'description');
        $metric->record(5);
        $this->assertEquals(1, $metric->getCount());
        $this->assertEquals(5, $metric->getMax());
        $this->assertEquals(5, $metric->getMin());
        $this->assertEquals(5, $metric->getSum());
        $metric->record(-2);
        $this->assertEquals(2, $metric->getCount());
        $this->assertEquals(5, $metric->getMax());
        $this->assertEquals(-2, $metric->getMin());
        $this->assertEquals(3, $metric->getSum());
    }

    public function test_valid_negative_and_positive_record(): void
    {
        $metric = new ValueRecorder('name', 'description');
        $metric->record(-5);
        $this->assertEquals(1, $metric->getCount());
        $this->assertEquals(-5, $metric->getMax());
        $this->assertEquals(-5, $metric->getMin());
        $this->assertEquals(-5, $metric->getSum());
        $metric->record(2);
        $this->assertEquals(2, $metric->getCount());
        $this->assertEquals(2, $metric->getMax());
        $this->assertEquals(-5, $metric->getMin());
        $this->assertEquals(-3, $metric->getSum());
    }

    public function test_valid_positive_float_record(): void
    {
        $metric = new ValueRecorder('name', 'description');
        $metric->record(5.2222);
        $this->assertEquals(1, $metric->getCount());
        $this->assertEquals(5.2222, $metric->getMax());
        $this->assertEquals(5.2222, $metric->getMin());
        $this->assertEquals(5.2222, $metric->getSum());
        $metric->record(2.6666);
        $this->assertEquals(2, $metric->getCount());
        $this->assertEquals(5.2222, $metric->getMax());
        $this->assertEquals(2.6666, $metric->getMin());
        $this->assertEquals(7.8888, $metric->getSum());
    }

    public function test_valid_negative_float_record(): void
    {
        $metric = new ValueRecorder('name', 'description');
        $metric->record(-5.2222);
        $this->assertEquals(1, $metric->getCount());
        $this->assertEquals(-5.2222, $metric->getMax());
        $this->assertEquals(-5.2222, $metric->getMin());
        $this->assertEquals(-5.2222, $metric->getSum());
        $metric->record(-2.6666);
        $this->assertEquals(2, $metric->getCount());
        $this->assertEquals(-2.6666, $metric->getMax());
        $this->assertEquals(-5.2222, $metric->getMin());
        $this->assertEquals(-7.8888, $metric->getSum());
    }

    public function test_valid_positive_and_negative_float_record(): void
    {
        $metric = new ValueRecorder('name', 'description');
        $metric->record(5.2222);
        $this->assertEquals(1, $metric->getCount());
        $this->assertEquals(5.2222, $metric->getMax());
        $this->assertEquals(5.2222, $metric->getMin());
        $this->assertEquals(5.2222, $metric->getSum());
        $metric->record(-2.6666);
        $this->assertEquals(2, $metric->getCount());
        $this->assertEquals(5.2222, $metric->getMax());
        $this->assertEquals(-2.6666, $metric->getMin());
        $this->assertEquals(2.5556, $metric->getSum());
    }

    public function test_valid_negative_and_positive_float_record(): void
    {
        $metric = new ValueRecorder('name', 'description');
        $metric->record(-5.2222);
        $this->assertEquals(1, $metric->getCount());
        $this->assertEquals(-5.2222, $metric->getMax());
        $this->assertEquals(-5.2222, $metric->getMin());
        $this->assertEquals(-5.2222, $metric->getSum());
        $metric->record(2.6666);
        $this->assertEquals(2, $metric->getCount());
        $this->assertEquals(2.6666, $metric->getMax());
        $this->assertEquals(-5.2222, $metric->getMin());
        $this->assertEquals(-2.5556, $metric->getSum());
    }

    public function test_value_recorder_initialization(): void
    {
        $metric = new ValueRecorder('name', 'description');
        $this->assertEquals(0, $metric->getCount());
        $this->assertEquals(INF, $metric->getMax());
        $this->assertEquals(-INF, $metric->getMin());
        $this->assertEquals(0, $metric->getSum());
        $this->assertEquals(0, $metric->getMean());
    }

    public function test_invalid_value_recorder_record_throws_exception(): void
    {
        $metric = new ValueRecorder('name', 'description');
        $this->expectException(InvalidArgumentException::class);
        /**
         * @phpstan-ignore-next-line
         * @psalm-suppress InvalidScalarArgument
         */
        $metric->record('a');
    }
}
