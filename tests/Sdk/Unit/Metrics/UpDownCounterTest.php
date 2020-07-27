<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Metrics;

use InvalidArgumentException;
use OpenTelemetry\Sdk\Metrics\UpDownCounter;
use PHPUnit\Framework\TestCase;

class UpDownCounterTest extends TestCase
{
    /**
     * @test
     */
    public function testValidPositiveIntAdd()
    {
        $counter = new UpDownCounter('name', 'description');
        $retVal = $counter->Add(5);
        $this->assertEquals(5, $retVal);
        $retVal = $counter->Add(2);
        $this->assertEquals(7, $retVal);
    }
    public function testValidNegativeIntAdd()
    {
        $counter = new UpDownCounter('name', 'description');
        $retVal = $counter->Add(-5);
        $this->assertEquals(-5, $retVal);
        $retVal = $counter->Add(-2);
        $this->assertEquals(-7, $retVal);
    }

    public function testValidPositiveAndNegativeIntAdd()
    {
        $counter = new UpDownCounter('name', 'description');
        $retVal = $counter->Add(5);
        $this->assertEquals(5, $retVal);
        $retVal = $counter->Add(-2);
        $this->assertEquals(3, $retVal);
    }
    public function testValidNegativeAndPositiveAdd()
    {
        $counter = new UpDownCounter('name', 'description');
        $retVal = $counter->Add(-5);
        $this->assertEquals(-5, $retVal);
        $retVal = $counter->Add(2);
        $this->assertEquals(-3, $retVal);
    }

    public function testValidPositiveFloastAdd()
    {
        $counter = new UpDownCounter('name', 'description');
        $retVal = $counter->Add(5.2222);
        $this->assertEquals(5, $retVal);
        $retVal = $counter->Add(2.6666);
        $this->assertEquals(7, $retVal);
    }
    public function testValidNegativeFloatAdd()
    {
        $counter = new UpDownCounter('name', 'description');
        $retVal = $counter->Add(-5.2222);
        $this->assertEquals(-5, $retVal);
        $retVal = $counter->Add(-2.6666);
        $this->assertEquals(-7, $retVal);
    }

    public function testValidPositiveAndNegativeFloatAdd()
    {
        $counter = new UpDownCounter('name', 'description');
        $retVal = $counter->Add(5.2222);
        $this->assertEquals(5, $retVal);
        $retVal = $counter->Add(-2.6666);
        $this->assertEquals(3, $retVal);
    }
    public function testValidNegativeAndPositiveFloatAdd()
    {
        $counter = new UpDownCounter('name', 'description');
        $retVal = $counter->Add(-5.2222);
        $this->assertEquals(-5, $retVal);
        $retVal = $counter->Add(2.6666);
        $this->assertEquals(-3, $retVal);
    }
    public function testInvalidUpDownCounterAddThrowsException()
    {
        $counter = new UpDownCounter('name', 'description');
        $this->expectException(InvalidArgumentException::class);
        $retVal = $counter->Add('a');
    }
}
