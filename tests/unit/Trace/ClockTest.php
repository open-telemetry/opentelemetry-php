<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Tests;

use OpenTelemetry\Sdk\Trace\Clock;
use PHPUnit\Framework\TestCase;

class InternalClockTest extends TestCase
{
    /**
     * @test
     */
    public function testReturnetStringRepresentMilliseconds()
    {
        $clock = new Clock();
        $milliseconds = $clock->timestamp();

        $this->assertGreaterThan(1e12, $milliseconds);
    }
}
