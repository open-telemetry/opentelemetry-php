<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Common\Time;

use OpenTelemetry\API\Common\Time\Util;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\API\Common\Time\Util
 */
class UtilTest extends TestCase
{
    public function test_nanos_to_micro(): void
    {
        $this->assertEquals(1, Util::nanosToMicros((int) 1e3));
    }

    public function test_nanos_to_milli(): void
    {
        $this->assertEquals(1, Util::nanosToMillis((int) 1e6));
    }

    public function test_seconds_to_nanos(): void
    {
        $this->assertEquals((int) 1e9, Util::secondsToNanos(1));
    }

    public function test_millis_to_nanos(): void
    {
        $this->assertEquals((int) 1e6, Util::millisToNanos(1));
    }
}
