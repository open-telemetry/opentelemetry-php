<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Time;

use OpenTelemetry\SDK\Common\Time\StopWatch;
use OpenTelemetry\Tests\Unit\SDK\Util\TestClock;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Common\Time\StopWatch
 */
class StopWatchTest extends TestCase
{
    private StopWatch $stopwatch;
    private TestClock $testClock;

    public function setUp(): void
    {
        $this->testClock = new TestClock();
        $this->stopwatch = new StopWatch(
            $this->testClock
        );
    }

    public function test_is_not_running_initially(): void
    {
        $this->assertFalse($this->stopwatch->isRunning());
    }

    public function test_start(): void
    {
        $this->stopwatch->start();

        $this->assertTrue($this->stopwatch->isRunning());
    }

    public function test_restart(): void
    {
        $this->stopwatch->start();
        $this->stopwatch->stop();
        $this->stopwatch->start();

        $this->assertTrue($this->stopwatch->isRunning());
    }

    public function test_stop(): void
    {
        $this->stopwatch->start();
        $this->stopwatch->stop();
        $this->assertFalse($this->stopwatch->isRunning());
    }

    public function test_stop_without_start(): void
    {
        $this->stopwatch->stop();

        $this->assertFalse($this->stopwatch->isRunning());
    }

    public function test_get_elapsed_time_initially(): void
    {
        $this->assertSame(0, $this->stopwatch->getElapsedTime());
    }

    public function test_get_elapsed_time_started(): void
    {
        $elapsed = 500;

        $this->stopwatch->start();
        $this->testClock->advance($elapsed);

        $this->assertSame($elapsed, $this->stopwatch->getElapsedTime());
    }

    public function test_get_elapsed_time_started_twice(): void
    {
        $elapsed = 500;

        $this->stopwatch->start();
        $this->testClock->advance($elapsed);
        $this->stopwatch->start();
        $this->testClock->advance($elapsed);

        $this->assertSame($elapsed * 2, $this->stopwatch->getElapsedTime());
    }

    public function test_get_elapsed_time_stopped(): void
    {
        $elapsed = 500;

        $this->stopwatch->start();
        $this->testClock->advance($elapsed);
        $this->stopwatch->stop();
        $this->testClock->advance($elapsed);

        $this->assertSame($elapsed, $this->stopwatch->getElapsedTime());
    }

    public function test_get_elapsed_time_stopped_twice(): void
    {
        $elapsed = 500;

        $this->stopwatch->start();
        $this->testClock->advance($elapsed);
        $this->stopwatch->stop();
        $this->testClock->advance($elapsed);
        $this->stopwatch->stop();

        $this->assertSame($elapsed, $this->stopwatch->getElapsedTime());
    }

    public function test_get_last_elapsed_time_initially(): void
    {
        $this->assertSame(0, $this->stopwatch->getLastElapsedTime());
    }

    public function test_get_last_elapsed_time_started(): void
    {
        $elapsed = 500;

        $this->stopwatch->start();
        $this->testClock->advance($elapsed);

        $this->assertSame($elapsed, $this->stopwatch->getLastElapsedTime());
    }

    public function test_get_last_elapsed_time_started_twice(): void
    {
        $elapsed = 500;

        $this->stopwatch->start();
        $this->testClock->advance($elapsed);
        $this->stopwatch->start();
        $this->testClock->advance($elapsed);

        $this->assertSame($elapsed * 2, $this->stopwatch->getLastElapsedTime());
    }

    public function test_get_last_elapsed_time_restarted(): void
    {
        $elapsed = 500;

        $this->stopwatch->start();
        $this->testClock->advance($elapsed);
        $this->stopwatch->stop();
        $this->testClock->advance($elapsed);
        $this->stopwatch->start();
        $this->testClock->advance($elapsed);

        $this->assertSame($elapsed, $this->stopwatch->getLastElapsedTime());
    }

    public function test_get_last_elapsed_time_stopped(): void
    {
        $elapsed = 500;

        $this->stopwatch->start();
        $this->testClock->advance($elapsed);
        $this->stopwatch->stop();
        $this->testClock->advance($elapsed);

        $this->assertSame($elapsed, $this->stopwatch->getLastElapsedTime());
    }

    public function test_get_last_elapsed_time_stopped_twice(): void
    {
        $elapsed = 500;

        $this->stopwatch->start();
        $this->testClock->advance($elapsed);
        $this->stopwatch->stop();
        $this->testClock->advance($elapsed);
        $this->stopwatch->stop();

        $this->assertSame($elapsed, $this->stopwatch->getLastElapsedTime());
    }

    public function test_reset_initially(): void
    {
        $this->stopwatch->reset();
        $this->testClock->advance(500);

        $this->assertSame(0, $this->stopwatch->getElapsedTime());
        $this->assertSame(0, $this->stopwatch->getLastElapsedTime());
    }

    public function test_reset_started(): void
    {
        $elapsed = 500;

        $this->stopwatch->start();
        $this->testClock->advance($elapsed);
        $this->stopwatch->reset();
        $this->testClock->advance($elapsed);

        $this->assertSame($elapsed, $this->stopwatch->getElapsedTime());
        $this->assertSame($elapsed, $this->stopwatch->getLastElapsedTime());
    }

    public function test_reset_stopped(): void
    {
        $elapsed = 500;

        $this->stopwatch->start();
        $this->testClock->advance($elapsed);
        $this->stopwatch->stop();
        $this->stopwatch->reset();
        $this->testClock->advance($elapsed);

        $this->assertSame(0, $this->stopwatch->getElapsedTime());
        $this->assertSame(0, $this->stopwatch->getLastElapsedTime());
    }
}
