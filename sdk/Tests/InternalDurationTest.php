<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Tests;

use OpenTelemetry\Sdk\Internal\Duration;
use OpenTelemetry\Sdk\Internal\Time;
use OpenTelemetry\Sdk\Internal\Timestamp;
use PHPUnit\Framework\TestCase;

class InternalDurationTest extends TestCase
{
    public function testOf()
    {
        $duration = Duration::of(137);
        $this->assertEquals(137, $duration->to(Time::NANOSECOND));
    }

    public function testBetween()
    {
        $start = Timestamp::at(30);
        $end = Timestamp::at(45);
        $duration = Duration::between($start, $end);

        $this->assertEquals(15, $duration->to(Time::NANOSECOND));
    }
}
