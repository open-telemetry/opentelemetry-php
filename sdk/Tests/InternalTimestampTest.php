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

        $this->assertGreaterThanOrEqual(
            19,
            strlen(sprintf('%d', $timestamp->to(Time::NANOSECOND))),
            'Timestamp for current Unix time has at least 19 digits'
        );
    }

    public function testAt()
    {
        $timestamp = Timestamp::at(7);

        $this->assertEquals(7, $timestamp->to(Time::NANOSECOND));
    }
}
