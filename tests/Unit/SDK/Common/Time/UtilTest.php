<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Time;

use OpenTelemetry\SDK\Common\Time\Util;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Common\Time\Util
 */
class UtilTest extends TestCase
{
    public function test_nanos_to_micro(): void
    {
        $this->assertEquals(1, Util::nanosToMicro((int) 1e3));
    }

    public function test_nanos_to_milli(): void
    {
        $this->assertEquals(1, Util::nanosToMilli((int) 1e6));
    }

    public function test_seconds_to_nanos(): void
    {
        $this->assertEquals((int) 1e9, Util::secondsToNanos(1));
    }
}
