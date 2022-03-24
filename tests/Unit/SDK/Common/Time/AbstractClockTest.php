<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Time;

use OpenTelemetry\SDK\Common\Time\AbstractClock;
use OpenTelemetry\SDK\Common\Time\ClockInterface;
use OpenTelemetry\SDK\Common\Time\SystemClock;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Common\Time\AbstractClock
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
}
