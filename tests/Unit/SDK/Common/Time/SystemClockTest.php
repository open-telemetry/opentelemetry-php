<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Time;

use DateTime;
use OpenTelemetry\SDK\Common\Time\SystemClock;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Common\Time\SystemClock
 */
class SystemClockTest extends TestCase
{
    private const NANOS_PER_SECOND = 1_000_000_000;

    public function test_get_instance_always_returns_same_clock(): void
    {
        $this->assertSame(SystemClock::getInstance(), SystemClock::getInstance());
    }

    public function test_now_is_chronological(): void
    {
        $time1 = SystemClock::create()->now();
        usleep(1);
        $time2 = SystemClock::create()->now();

        $this->assertGreaterThan($time1, $time2);
    }

    public function test_now_returns_nanoseconds(): void
    {
        $this->assertNanoSecondsWallClock(
            SystemClock::create()->now(),
            new DateTime()
        );
    }

    public function test_nano_time_is_chronological(): void
    {
        $time1 = SystemClock::create()->nanoTime();
        usleep(1);
        $time2 = SystemClock::create()->nanoTime();

        $this->assertGreaterThan($time1, $time2);
    }

    public function test_nano_time_returns_nanoseconds(): void
    {
        $this->assertNanoSecondsWallClock(
            SystemClock::create()->nanoTime(),
            new DateTime()
        );
    }

    private function assertNanoSecondsWallClock(int $value, DateTime $reference): void
    {
        $this->assertGreaterThan(self::NANOS_PER_SECOND, $value);
        $tested = (new DateTime())->setTimestamp((int) ($value / self::NANOS_PER_SECOND));
        $interval = $tested->diff($reference);
        // Make sure to avoid edge cases, so check for difference of 0 OR 1
        $this->assertTrue($interval->days === 0 || $interval->days === 1);
        $this->assertTrue($interval->h === 0 || $interval->h === 1);
        $this->assertTrue($interval->m === 0 || $interval->m === 1);
    }
}
