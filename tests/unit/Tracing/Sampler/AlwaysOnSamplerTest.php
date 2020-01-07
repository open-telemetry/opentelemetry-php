<?php

namespace OpenTelemetry\Tests\Unit\Tracing\Sampler;

use OpenTelemetry\Trace\Sampler\AlwaysOnSampler;
use PHPUnit\Framework\TestCase;

class AlwaysOnTest extends TestCase
{
    public function testAlwaysOnSampler()
    {
        $sampler = new AlwaysOnSampler();
        $this->assertTrue($sampler->shouldSample());
    }
}
