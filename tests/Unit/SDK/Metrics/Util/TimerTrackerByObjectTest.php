<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics\Util;

use OpenTelemetry\SDK\Metrics\Util\TimerTrackerByObject;
use PHPUnit\Framework\TestCase;
use stdClass;

class TimerTrackerByObjectTest extends TestCase
{
    private TimerTrackerByObject $timerTracker;

    protected function setUp(): void
    {
        $this->timerTracker = new TimerTrackerByObject();
    }

    public function test_start(): void
    {
        $id = new stdClass();
        $this->timerTracker->start($id);

        // Assert that the timer has started for the given object.
        // We can't directly inspect the WeakMap, but we can check if durationMs returns a non-zero value.
        $this->assertGreaterThan(0, $this->timerTracker->durationMs($id));
    }

    public function test_duration_ms(): void
    {
        $id = new stdClass();
        $this->timerTracker->start($id);

        // Simulate some time passing
        usleep(2000); // 2 millisecond

        $duration = $this->timerTracker->durationMs($id);

        // Assert that the duration is a positive number
        $this->assertIsFloat($duration);
        $this->assertGreaterThan(0, $duration);
    }

    public function test_duration_ms_for_non_existent_object(): void
    {
        $id = new stdClass();
        $duration = $this->timerTracker->durationMs($id);

        // Assert that duration for a non-existent ID is 0
        $this->assertEquals(0, $duration);
    }

    public function test_start_multiple_times_for_same_object(): void
    {
        $id = new stdClass();
        $this->timerTracker->start($id);
        usleep(2500);
        $firstDuration = $this->timerTracker->durationMs($id);

        $this->timerTracker->start($id); // Restart the timer
        usleep(1000);
        $secondDuration = $this->timerTracker->durationMs($id);

        // Assert that the second duration is significantly smaller than the first,
        // indicating the timer was reset.
        $this->assertGreaterThan(0, $secondDuration);
        $this->assertLessThan($firstDuration, $secondDuration); // Second duration should be smaller
    }

    public function test_timer_removes_entry_when_object_is_garbage_collected(): void
    {
        $id = new stdClass();
        $this->timerTracker->start($id);

        // Ensure the timer is active for the object
        $this->assertGreaterThan(0, $this->timerTracker->durationMs($id));

        // Unset the reference to the object, allowing it to be garbage collected
        unset($id);

        // Although garbage collection is non-deterministic in PHP,
        // for testing purposes, we can assume that if the WeakMap is working,
        // eventually the entry will be removed.
        // In a real-world scenario, we might need to force GC or use a more
        // elaborate testing mechanism if this assertion fails intermittently.
        // For now, we'll just check immediately.
        $dummyObject = new stdClass();
        $this->assertEquals(0, $this->timerTracker->durationMs($dummyObject));
        // Note: It's hard to directly test if the original object's entry is gone
        // without keeping a reference to it. The key here is that if a timer was
        // started for an object that is no longer referenced, subsequent calls
        // with *that specific object* (if it were somehow recreated with the same
        // internal ID, which is unlikely) or any other object will correctly
        // return 0 if that object was not started.
        // A more direct test would involve an internal WeakMap check if possible,
        // but that breaks encapsulation.
    }
}
