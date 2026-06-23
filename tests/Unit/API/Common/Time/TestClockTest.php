<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Common\Time;

use OpenTelemetry\API\Common\Time\ClockInterface;
use OpenTelemetry\API\Common\Time\TestClock;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TestClock::class)]
class TestClockTest extends TestCase
{
    public function test_default_start_epoch(): void
    {
        $clock = new TestClock();
        $this->assertSame(TestClock::DEFAULT_START_EPOCH, $clock->now());
    }

    public function test_custom_start_epoch(): void
    {
        $clock = new TestClock(42);
        $this->assertSame(42, $clock->now());
    }

    public function test_advance(): void
    {
        $clock = new TestClock(100);
        $clock->advance(50);
        $this->assertSame(150, $clock->now());
    }

    public function test_advance_default_one_nano(): void
    {
        $clock = new TestClock(100);
        $clock->advance();
        $this->assertSame(101, $clock->now());
    }

    public function test_advance_seconds(): void
    {
        $clock = new TestClock(0);
        $clock->advanceSeconds(2);
        $this->assertSame(2 * ClockInterface::NANOS_PER_SECOND, $clock->now());
    }

    public function test_advance_seconds_default_one(): void
    {
        $clock = new TestClock(0);
        $clock->advanceSeconds();
        $this->assertSame(ClockInterface::NANOS_PER_SECOND, $clock->now());
    }

    public function test_set_time(): void
    {
        $clock = new TestClock(100);
        $clock->setTime(999);
        $this->assertSame(999, $clock->now());
    }
}
