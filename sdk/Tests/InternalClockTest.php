<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Tests;

use OpenTelemetry\Sdk\Internal\Clock;
use PHPUnit\Framework\TestCase;

class InternalClockTest extends TestCase
{
    /**
     * @test
     */
    public function testReturnetStringRepresentMilliseconds()
    {
        $clock = new Clock();
        $milliseconds = $clock->zipkinFormattedTime();

        $this->assertGreaterThan(1e12, (float) $milliseconds);
    }
}
