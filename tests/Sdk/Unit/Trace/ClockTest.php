<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Trace;

use OpenTelemetry\Sdk\Trace\Clock;
use PHPUnit\Framework\TestCase;

class ClockTest extends TestCase
{
    /**
     * @test
     */
    public function testReturnedTimestampStringRepresentMilliseconds()
    {
        $timestamp = Clock::get()->timestamp();
        $this->assertGreaterThan(1e12, $timestamp);
    }
}
