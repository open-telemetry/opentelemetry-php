<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Metrics;

use InvalidArgumentException;
use OpenTelemetry\Sdk\Metrics\ValueRecorder;
use PHPUnit\Framework\TestCase;

class ValueRecorderTest extends TestCase
{
    /**
     * @test
     */
    public function testValidPositiveIntRecord()
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

    public function testValidNegativeIntRecord()
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

    public function testValidPositiveAndNegativeIntRecord()
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

    public function testValidNegativeAndPositiveRecord()
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

    public function testValidPositiveFloastRecord()
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

    public function testValidNegativeFloatRecord()
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

    public function testValidPositiveAndNegativeFloatRecord()
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

    public function testValidNegativeAndPositiveFloatRecord()
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

    public function testValueRecorderInitialization()
    {
        $metric = new ValueRecorder('name', 'description');
        $this->assertEquals(0, $metric->getCount());
        $this->assertEquals(INF, $metric->getMax());
        $this->assertEquals(-INF, $metric->getMin());
        $this->assertEquals(0, $metric->getSum());
        $this->assertEquals(0, $metric->getMean());
    }

    public function testInvalidValueRecorderRecordThrowsException()
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
