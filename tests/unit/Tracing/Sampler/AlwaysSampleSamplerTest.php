<?php

require __DIR__.'/../../../../vendor/autoload.php';

use OpenTelemetry\Tracing\Sampler\AlwaysSampleSampler;
use PHPUnit\Framework\TestCase;

class AlwaysSamplerTest extends TestCase
{
    public function testAlwaysSampler()
    {
        $sampler = new AlwaysSampleSampler();
        $this->assertTrue($sampler->shouldSample());
    }
}