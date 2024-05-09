<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Common\Time;

use DateTime;
use OpenTelemetry\API\Common\Time\SystemClock;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\OpenTelemetry\API\Common\Time\SystemClock::class)]
class SystemClockTest extends TestCase
{
    private const NANOS_PER_SECOND = 1_000_000_000;

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
