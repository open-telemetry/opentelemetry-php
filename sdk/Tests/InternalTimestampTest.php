<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Tests;

use OpenTelemetry\Sdk\Internal\Time;
use OpenTelemetry\Sdk\Internal\Timestamp;
use PHPUnit\Framework\TestCase;

class InternalTimestampTest extends TestCase
{
    public function testNow()
    {
        $timestamp = Timestamp::now();

        $this->assertGreaterThan(1e15, $timestamp->to(Time::NANOSECOND), 'Timestamp for current Unix time has at least 15 digits');
    }

    public function testAt()
    {
        $timetamp = Timestamp::at(7);

        $this->assertEquals(7, $timetamp->to(Time::NANOSECOND));
    }
}
