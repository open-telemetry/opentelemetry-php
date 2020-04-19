<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Tests;

use OpenTelemetry\Sdk\Internal\Time;
use PHPUnit\Framework\TestCase;

class InternalTimeTest extends TestCase
{
    public function testTo()
    {
        $time = $this->createTime(1234567890987654321);
        // seconds a milliseconds are rounded up
        $this->assertEquals(1234567891, $time->to(Time::SECOND));
        $this->assertEquals(1234567890988, $time->to(Time::MILLISECOND));
        $this->assertEquals(1234567890987654, $time->to(Time::MICROSECOND));
        $this->assertEquals(1234567890987654321, $time->to(Time::NANOSECOND));
    }

    private function createTime(int $time)
    {
        return new class($time) extends Time {
            public function __construct(int $time)
            {
                parent::__construct($time);
            }
        };
    }
}
