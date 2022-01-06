<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK;

use OpenTelemetry\SDK\SystemClock;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\SDK\SystemClock
 */
class SystemClockTest extends TestCase
{
    /**
     * @runInSeparateProcess
     * @preserveGlobalState false
     */
    public function test_get_instance_always_returns_same_clock(): void
    {
        $clock = SystemClock::getInstance();
        $this->assertSame($clock, SystemClock::getInstance());
    }
}
