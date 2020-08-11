<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Metrics;

use OpenTelemetry\Sdk\Metrics\Counter;
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
}
