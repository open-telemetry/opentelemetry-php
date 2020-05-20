<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Tests;

use OpenTelemetry\Sdk\Trace\Clock;
use PHPUnit\Framework\TestCase;

class ClockTest extends TestCase
{
    /**
     * @test
     */
    public function testReturnetStringRepresentMilliseconds()
    {
        $clock = Clock::get()->moment();
        $this->assertGreaterThan(1e12, (float) $clock[1]);
    }
}
