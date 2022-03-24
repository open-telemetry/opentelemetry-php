<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Time;

use OpenTelemetry\SDK\Common\Time\SystemClock;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Common\Time\SystemClock
 */
class SystemClockTest extends TestCase
{
    public function test_get_instance_always_returns_same_clock(): void
    {
        $clock = SystemClock::getInstance();
        $this->assertSame($clock, SystemClock::getInstance());
    }
}
