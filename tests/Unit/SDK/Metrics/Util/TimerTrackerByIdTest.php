<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics\Util;

use OpenTelemetry\SDK\Metrics\Util\TimerTrackerById;
use PHPUnit\Framework\TestCase;

class TimerTrackerByIdTest extends TestCase
{
    private TimerTrackerById $timerTracker;

    protected function setUp(): void
    {
        $this->timerTracker = new TimerTrackerById();
    }

    public function test_start(): void
    {
        $id = 'test_id';
        $this->timerTracker->start($id);

        // Assert that the timer has started for the given ID.
        // This might involve checking an internal state if available,
        // or simply ensuring no error is thrown.
        // For now, we'll assume a subsequent call to durationMs will indicate success.
        $this->assertGreaterThan(0, $this->timerTracker->durationMs($id));
    }

    public function test_duration_ms(): void
    {
        $id = 'test_id_duration';
        $this->timerTracker->start($id);

        // Simulate some time passing
        usleep(2000); // 2 millisecond

        $duration = $this->timerTracker->durationMs($id);

        // Assert that the duration is a positive number
        $this->assertIsFloat($duration);
        $this->assertGreaterThan(0, $duration);
    }

    public function test_duration_ms_for_non_existent_id(): void
    {
        $id = 'non_existent_id';
        $duration = $this->timerTracker->durationMs($id);

        // Assert that duration for a non-existent ID is 0 or null, depending on implementation
        $this->assertEquals(0, $duration);
    }

    public function test_start_multiple_times_for_same_id(): void
    {
        $id = 'test_id_multiple';
        $this->timerTracker->start($id);
        usleep(2500);
        $firstDuration = $this->timerTracker->durationMs($id);

        $this->timerTracker->start($id); // Restart the timer
        usleep(2000);
        $secondDuration = $this->timerTracker->durationMs($id);

        // Assert that the second duration is significantly smaller than the first,
        // indicating the timer was reset.
        $this->assertGreaterThan(0, $secondDuration);
        $this->assertLessThan($firstDuration, $secondDuration); // Second duration should be smaller
    }
}
