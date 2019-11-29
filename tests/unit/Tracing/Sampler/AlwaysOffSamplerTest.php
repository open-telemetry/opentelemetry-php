<?php
require __DIR__.'/../../../../vendor/autoload.php';
use OpenTelemetry\Tracing\Sampler\AlwaysOffSampler;
use PHPUnit\Framework\TestCase;
class AlwaysOffSamplerTest extends TestCase
{
    public function testAlwaysOffSampler()
    {
        $sampler = new AlwaysOffSampler();
        $this->assertFalse($sampler->shouldSample());
    }
} 
