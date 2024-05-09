<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Common\Time;

use OpenTelemetry\API\Common\Time\Clock;
use OpenTelemetry\API\Common\Time\ClockInterface;
use OpenTelemetry\API\Common\Time\SystemClock;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\API\Common\Time\Clock
 */
class ClockTest extends TestCase
{
    public function setUp(): void
    {
        Clock::reset();
    }

    public function test_default_is_system_clock(): void
    {
        $this->assertInstanceOf(SystemClock::class, Clock::getDefault());
    }

    public function test_default_is_settable(): void
    {
        $clock = $this->createMock(ClockInterface::class);
        Clock::setDefault($clock);

        $this->assertSame($clock, Clock::getDefault());
    }

    public function test_default_is_resettable(): void
    {
        $clock = $this->createMock(ClockInterface::class);
        Clock::setDefault(
            $clock
        );
        Clock::reset();

        $this->assertNotSame($clock, Clock::getDefault());
    }
}
