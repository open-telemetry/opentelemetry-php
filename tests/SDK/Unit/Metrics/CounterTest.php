<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Metrics;

use OpenTelemetry\SDK\Metrics\Counter;
use PHPUnit\Framework\TestCase;

class CounterTest extends TestCase
{
    public function test_counter_increments(): void
    {
        $counter = new Counter('some_counter');

        $this->assertSame(0, $counter->getValue());

        $counter->increment();

        $this->assertSame(1, $counter->getValue());
    }

    public function test_counter_does_not_accept_negative_numbers(): void
    {
        $counter = new Counter('some_counter');

        $this->expectException(\InvalidArgumentException::class);

        $counter->add(-1);
    }
}
