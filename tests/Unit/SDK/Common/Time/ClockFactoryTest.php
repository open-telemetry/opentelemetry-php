<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Time;

use OpenTelemetry\SDK\Common\Time\ClockFactory;
use OpenTelemetry\SDK\Common\Time\ClockInterface;
use OpenTelemetry\SDK\Common\Time\SystemClock;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Common\Time\ClockFactory
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

        $this->assertEquals($clock, ClockFactory::getDefault());
    }

    public function test_default_is_resettable(): void
    {
        ClockFactory::setDefault(
            $this->createMock(ClockInterface::class)
        );
        ClockFactory::setDefault(null);

        $this->assertInstanceOf(SystemClock::class, ClockFactory::getDefault());
    }
}
