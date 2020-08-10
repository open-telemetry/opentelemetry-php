<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Metrics;

use OpenTelemetry\Sdk\Metrics\Counter;
use OpenTelemetry\Sdk\Metrics\UpDownCounter;
use PHPUnit\Framework\TestCase;

class CounterTest extends TestCase
{
    public function testCounterIncrements()
    {
        $counter = new Counter('some_counter');

        $this->assertSame(0, $counter->getValue());

        $counter->increment();

        $this->assertSame(1, $counter->getValue());
    }

    public function testCounterDoesNotAcceptNegativeNumbers()
    {
        $counter = new Counter('some_counter');

        $this->expectException(\InvalidArgumentException::class);

        $counter->add(-1);
    }

    public function testUpDownCounterIncrementsAndDecrements()
    {
        $counter = new UpDownCounter('some_counter');

        $this->assertSame(0, $counter->getValue());

        $counter->increment();

        $this->assertSame(1, $counter->getValue());

        $counter->decrement();

        $this->assertSame(0, $counter->getValue());
    }

    public function testUpDownCounterAcceptNegativeNumbers()
    {
        $counter = new UpDownCounter('some_up_down_counter');

        $this->assertSame(0, $counter->getValue());

        $counter->add(-1);

        $this->assertSame(-1, $counter->getValue());

        $counter->subtract(3);

        $this->assertSame(-4, $counter->getValue());
    }
}
