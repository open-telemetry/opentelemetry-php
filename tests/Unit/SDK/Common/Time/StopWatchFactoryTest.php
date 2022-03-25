<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Time;

use OpenTelemetry\SDK\Common\Time\ClockFactoryInterface;
use OpenTelemetry\SDK\Common\Time\StopWatch;
use OpenTelemetry\SDK\Common\Time\StopWatchFactory;
use OpenTelemetry\SDK\Common\Time\StopWatchInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Common\Time\StopWatchFactory
 */

class StopWatchFactoryTest extends TestCase
{
    public function test_from_clock_factory(): void
    {
        $clockFactory = $this->createMock(ClockFactoryInterface::class);
        $clockFactory->expects($this->once())->method('build');

        StopWatchFactory::fromClockFactory($clockFactory);
    }

    public function test_default_is_system_clock(): void
    {
        $this->assertInstanceOf(StopWatch::class, StopWatchFactory::getDefault());
    }

    public function test_default_is_settable(): void
    {
        $stopwatch = $this->createMock(StopWatchInterface::class);
        StopWatchFactory::setDefault($stopwatch);

        $this->assertEquals($stopwatch, StopWatchFactory::getDefault());
    }

    public function test_default_is_resettable(): void
    {
        StopWatchFactory::setDefault(
            $this->createMock(StopWatchInterface::class)
        );
        StopWatchFactory::setDefault(null);

        $this->assertInstanceOf(StopWatch::class, StopWatchFactory::getDefault());
    }
}
