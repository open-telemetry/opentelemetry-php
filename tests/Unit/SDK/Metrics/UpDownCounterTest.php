<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics;

use InvalidArgumentException;
use OpenTelemetry\API\Metrics as API;
use OpenTelemetry\SDK\Metrics\UpDownCounter;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\SDK\Metrics\UpDownCounter
 */
class UpDownCounterTest extends TestCase
{
    private UpDownCounter $counter;

    public function setUp(): void
    {
        $this->counter = new UpDownCounter('name', 'description');
    }

    public function test_get_type(): void
    {
        $this->assertSame(API\MetricKind::UP_DOWN_COUNTER, $this->counter->getType());
    }

    public function test_get_value(): void
    {
        $this->counter->add(1);
        $this->assertSame(1, $this->counter->getValue());
    }

    public function test_valid_positive_int_add(): void
    {
        $retVal = $this->counter->add(5);
        $this->assertEquals(5, $retVal);
        $retVal = $this->counter->add(2);
        $this->assertEquals(7, $retVal);
    }
    public function test_valid_negative_int_add(): void
    {
        $retVal = $this->counter->add(-5);
        $this->assertEquals(-5, $retVal);
        $retVal = $this->counter->add(-2);
        $this->assertEquals(-7, $retVal);
    }

    public function test_valid_positive_and_negative_int_add(): void
    {
        $retVal = $this->counter->add(5);
        $this->assertEquals(5, $retVal);
        $retVal = $this->counter->add(-2);
        $this->assertEquals(3, $retVal);
    }
    public function test_valid_negative_and_positive_add(): void
    {
        $retVal = $this->counter->add(-5);
        $this->assertEquals(-5, $retVal);
        $retVal = $this->counter->add(2);
        $this->assertEquals(-3, $retVal);
    }

    public function test_valid_positive_float_add(): void
    {
        $retVal = $this->counter->add(5.2222);
        $this->assertEquals(5, $retVal);
        $retVal = $this->counter->add(2.6666);
        $this->assertEquals(7, $retVal);
    }
    public function test_valid_negative_float_add(): void
    {
        $retVal = $this->counter->add(-5.2222);
        $this->assertEquals(-5, $retVal);
        $retVal = $this->counter->add(-2.6666);
        $this->assertEquals(-7, $retVal);
    }

    public function test_valid_positive_and_negative_float_add(): void
    {
        $retVal = $this->counter->add(5.2222);
        $this->assertEquals(5, $retVal);
        $retVal = $this->counter->add(-2.6666);
        $this->assertEquals(3, $retVal);
    }
    public function test_valid_negative_and_positive_float_add(): void
    {
        $retVal = $this->counter->add(-5.2222);
        $this->assertEquals(-5, $retVal);
        $retVal = $this->counter->add(2.6666);
        $this->assertEquals(-3, $retVal);
    }
    public function test_invalid_up_down_counter_add_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        /**
         * @phpstan-ignore-next-line
         * @psalm-suppress InvalidScalarArgument
         */
        $this->counter->add('a');
    }
}
