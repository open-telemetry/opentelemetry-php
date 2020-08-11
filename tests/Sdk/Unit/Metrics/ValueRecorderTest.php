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
        $this->assertEquals(1, $metric->getValueCount());
        $this->assertEquals(5, $metric->getValueMax());
        $this->assertEquals(5, $metric->getValueMin());
        $this->assertEquals(5, $metric->getValueSum());
        $metric->record(2);
        $this->assertEquals(2, $metric->getValueCount());
        $this->assertEquals(5, $metric->getValueMax());
        $this->assertEquals(2, $metric->getValueMin());
        $this->assertEquals(7, $metric->getValueSum());
    }
    public function testValidNegativeIntRecord()
    {
        $metric = new ValueRecorder('name', 'description');
        $metric->record(-5);
        $this->assertEquals(1, $metric->getValueCount());
        $this->assertEquals(-5, $metric->getValueMax());
        $this->assertEquals(-5, $metric->getValueMin());
        $this->assertEquals(-5, $metric->getValueSum());
        $metric->record(-2);
        $this->assertEquals(2, $metric->getValueCount());
        $this->assertEquals(-2, $metric->getValueMax());
        $this->assertEquals(-5, $metric->getValueMin());
        $this->assertEquals(-7, $metric->getValueSum());
    }

    public function testValidPositiveAndNegativeIntRecord()
    {
        $metric = new ValueRecorder('name', 'description');
        $metric->record(5);
        $this->assertEquals(1, $metric->getValueCount());
        $this->assertEquals(5, $metric->getValueMax());
        $this->assertEquals(5, $metric->getValueMin());
        $this->assertEquals(5, $metric->getValueSum());
        $metric->record(-2);
        $this->assertEquals(2, $metric->getValueCount());
        $this->assertEquals(5, $metric->getValueMax());
        $this->assertEquals(-2, $metric->getValueMin());
        $this->assertEquals(3, $metric->getValueSum());
    }
    public function testValidNegativeAndPositiveRecord()
    {
        $metric = new ValueRecorder('name', 'description');
        $metric->record(-5);
        $this->assertEquals(1, $metric->getValueCount());
        $this->assertEquals(-5, $metric->getValueMax());
        $this->assertEquals(-5, $metric->getValueMin());
        $this->assertEquals(-5, $metric->getValueSum());
        $metric->record(2);
        $this->assertEquals(2, $metric->getValueCount());
        $this->assertEquals(2, $metric->getValueMax());
        $this->assertEquals(-5, $metric->getValueMin());
        $this->assertEquals(-3, $metric->getValueSum());
    }

    public function testValidPositiveFloastRecord()
    {
        $metric = new ValueRecorder('name', 'description');
        $metric->record(5.2222);
        $this->assertEquals(1, $metric->getValueCount());
        $this->assertEquals(5.2222, $metric->getValueMax());
        $this->assertEquals(5.2222, $metric->getValueMin());
        $this->assertEquals(5.2222, $metric->getValueSum());
        $metric->record(2.6666);
        $this->assertEquals(2, $metric->getValueCount());
        $this->assertEquals(5.2222, $metric->getValueMax());
        $this->assertEquals(2.6666, $metric->getValueMin());
        $this->assertEquals(7.8888, $metric->getValueSum());
    }
    public function testValidNegativeFloatRecord()
    {
        $metric = new ValueRecorder('name', 'description');
        $metric->record(-5.2222);
        $this->assertEquals(1, $metric->getValueCount());
        $this->assertEquals(-5.2222, $metric->getValueMax());
        $this->assertEquals(-5.2222, $metric->getValueMin());
        $this->assertEquals(-5.2222, $metric->getValueSum());
        $metric->record(-2.6666);
        $this->assertEquals(2, $metric->getValueCount());
        $this->assertEquals(-2.6666, $metric->getValueMax());
        $this->assertEquals(-5.2222, $metric->getValueMin());
        $this->assertEquals(-7.8888, $metric->getValueSum());
    }

    public function testValidPositiveAndNegativeFloatRecord()
    {
        $metric = new ValueRecorder('name', 'description');
        $metric->record(5.2222);
        $this->assertEquals(1, $metric->getValueCount());
        $this->assertEquals(5.2222, $metric->getValueMax());
        $this->assertEquals(5.2222, $metric->getValueMin());
        $this->assertEquals(5.2222, $metric->getValueSum());
        $metric->record(-2.6666, $metric->getValueCount());
        $this->assertEquals(2, $metric->getValueCount());
        $this->assertEquals(5.2222, $metric->getValueMax());
        $this->assertEquals(-2.6666, $metric->getValueMin());
        $this->assertEquals(2.5556, $metric->getValueSum());
    }
    public function testValidNegativeAndPositiveFloatRecord()
    {
        $metric = new ValueRecorder('name', 'description');
        $metric->record(-5.2222);
        $this->assertEquals(1, $metric->getValueCount());
        $this->assertEquals(-5.2222, $metric->getValueMax());
        $this->assertEquals(-5.2222, $metric->getValueMin());
        $this->assertEquals(-5.2222, $metric->getValueSum());
        $metric->record(2.6666);
        $this->assertEquals(2, $metric->getValueCount());
        $this->assertEquals(2.6666, $metric->getValueMax());
        $this->assertEquals(-5.2222, $metric->getValueMin());
        $this->assertEquals(-2.5556, $metric->getValueSum());
    }
    public function testInvalidValueRecorderRecordThrowsException()
    {
        $metric = new ValueRecorder('name', 'description');
        $this->expectException(InvalidArgumentException::class);
        $retVal = $metric->record('a');
    }
}
