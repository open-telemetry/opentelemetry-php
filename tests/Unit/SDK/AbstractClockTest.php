<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK;

use OpenTelemetry\SDK\AbstractClock;
use OpenTelemetry\SDK\ClockInterface;
use OpenTelemetry\SDK\SystemClock;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\SDK\AbstractClock
 */
class AbstractClockTest extends TestCase
{
    public function tearDown(): void
    {
        AbstractClock::setTestClock();
    }

    public function test_returns_default_system_clock(): void
    {
        $this->assertInstanceOf(SystemClock::class, AbstractClock::getDefault());
    }

    public function test_set_test_clock(): void
    {
        $testClock = $this->createMock(ClockInterface::class);
        AbstractClock::setTestClock($testClock);
        $this->assertSame($testClock, AbstractClock::getDefault());
    }

    public function test_conversions(): void
    {
        $this->assertEquals(1, AbstractClock::nanosToMicro((int) 1e3));
        $this->assertEquals(1, AbstractClock::nanosToMilli((int) 1e6));
        $this->assertEquals((int) 1e9, AbstractClock::secondsToNanos(1));
    }
}
