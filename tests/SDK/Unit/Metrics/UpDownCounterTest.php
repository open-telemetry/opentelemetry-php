<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Metrics;

use InvalidArgumentException;
use OpenTelemetry\SDK\Metrics\UpDownCounter;
use PHPUnit\Framework\TestCase;

class UpDownCounterTest extends TestCase
{
    /**
     * @test
     */
    public function test_valid_positive_int_add(): void
    {
        $counter = new UpDownCounter('name', 'description');
        $retVal = $counter->add(5);
        $this->assertEquals(5, $retVal);
        $retVal = $counter->add(2);
        $this->assertEquals(7, $retVal);
    }
    public function test_valid_negative_int_add(): void
    {
        $counter = new UpDownCounter('name', 'description');
        $retVal = $counter->add(-5);
        $this->assertEquals(-5, $retVal);
        $retVal = $counter->add(-2);
        $this->assertEquals(-7, $retVal);
    }

    public function test_valid_positive_and_negative_int_add(): void
    {
        $counter = new UpDownCounter('name', 'description');
        $retVal = $counter->add(5);
        $this->assertEquals(5, $retVal);
        $retVal = $counter->add(-2);
        $this->assertEquals(3, $retVal);
    }
    public function test_valid_negative_and_positive_add(): void
    {
        $counter = new UpDownCounter('name', 'description');
        $retVal = $counter->add(-5);
        $this->assertEquals(-5, $retVal);
        $retVal = $counter->add(2);
        $this->assertEquals(-3, $retVal);
    }

    public function test_valid_positive_float_add(): void
    {
        $counter = new UpDownCounter('name', 'description');
        $retVal = $counter->add(5.2222);
        $this->assertEquals(5, $retVal);
        $retVal = $counter->add(2.6666);
        $this->assertEquals(7, $retVal);
    }
    public function test_valid_negative_float_add(): void
    {
        $counter = new UpDownCounter('name', 'description');
        $retVal = $counter->add(-5.2222);
        $this->assertEquals(-5, $retVal);
        $retVal = $counter->add(-2.6666);
        $this->assertEquals(-7, $retVal);
    }

    public function test_valid_positive_and_negative_float_add(): void
    {
        $counter = new UpDownCounter('name', 'description');
        $retVal = $counter->add(5.2222);
        $this->assertEquals(5, $retVal);
        $retVal = $counter->add(-2.6666);
        $this->assertEquals(3, $retVal);
    }
    public function test_valid_negative_and_positive_float_add(): void
    {
        $counter = new UpDownCounter('name', 'description');
        $retVal = $counter->add(-5.2222);
        $this->assertEquals(-5, $retVal);
        $retVal = $counter->add(2.6666);
        $this->assertEquals(-3, $retVal);
    }
    public function test_invalid_up_down_counter_add_throws_exception(): void
    {
        $counter = new UpDownCounter('name', 'description');
        $this->expectException(InvalidArgumentException::class);
        /**
         * @phpstan-ignore-next-line
         * @psalm-suppress InvalidScalarArgument
         */
        $counter->add('a');
    }
}
