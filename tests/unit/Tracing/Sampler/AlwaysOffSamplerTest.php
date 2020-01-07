<?php
namespace OpenTelemetry\Tests\Unit\Tracing\Sampler;

use OpenTelemetry\Trace\Sampler\AlwaysOffSampler;
use PHPUnit\Framework\TestCase;
class AlwaysOffSamplerTest extends TestCase
{
    public function testAlwaysOffSampler()
    {
        $sampler = new AlwaysOffSampler();
        $this->assertFalse($sampler->shouldSample());
    }
} 
