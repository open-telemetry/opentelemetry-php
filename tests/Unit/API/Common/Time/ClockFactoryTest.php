<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Common\Time;

use OpenTelemetry\API\Common\Time\ClockFactory;
use OpenTelemetry\API\Common\Time\ClockInterface;
use OpenTelemetry\API\Common\Time\SystemClock;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\API\Common\Time\ClockFactory
 */
class ClockFactoryTest extends TestCase
{
    public function test_build(): void
    {
        $this->assertInstanceOf(SystemClock::class, ClockFactory::create()->build());
    }

    public function test_default_is_system_clock(): void
    {
        $this->assertInstanceOf(SystemClock::class, ClockFactory::getDefault());
    }

    public function test_default_is_settable(): void
    {
        $clock = $this->createMock(ClockInterface::class);
        ClockFactory::setDefault($clock);

        $this->assertSame($clock, ClockFactory::getDefault());
    }

    public function test_default_is_resettable(): void
    {
        $clock = $this->createMock(ClockInterface::class);
        ClockFactory::setDefault(
            $clock
        );
        ClockFactory::setDefault(null);

        $this->assertNotSame($clock, ClockFactory::getDefault());
    }
}
