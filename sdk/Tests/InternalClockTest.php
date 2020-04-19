<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Tests;

use OpenTelemetry\Sdk\Internal\Clock;
use OpenTelemetry\Sdk\Internal\Time;
use PHPUnit\Framework\TestCase;

class InternalClockTest extends TestCase
{
    /**
     * @test
     */
    public function testReturnedRepresentMilliseconds()
    {
        $clock = new Clock();
        $milliseconds = $clock->now()->to(Time::MILLISECOND);

        $this->assertGreaterThan(1e12, $milliseconds);
    }
}
