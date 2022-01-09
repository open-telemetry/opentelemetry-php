<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics;

use OpenTelemetry\API\Metrics as API;
use OpenTelemetry\SDK\Metrics\Counter;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\SDK\Metrics\Counter
 */
class CounterTest extends TestCase
{
    private Counter $counter;

    public function setUp(): void
    {
        $this->counter = new Counter('some_counter');
    }

    public function test_get_type(): void
    {
        $this->assertSame(API\MetricKind::COUNTER, $this->counter->getType());
    }

    public function test_counter_increments(): void
    {
        $this->assertSame(0, $this->counter->getValue());

        $this->counter->increment();

        $this->assertSame(1, $this->counter->getValue());
    }

    public function test_counter_does_not_accept_negative_numbers(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->counter->add(-1);
    }

    public function test_add(): void
    {
        $this->counter->add(5);
        $this->assertSame(5, $this->counter->getValue());
    }
}
