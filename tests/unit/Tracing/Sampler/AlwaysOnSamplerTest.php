<?php
require __DIR__.'/../../../../vendor/autoload.php';
use OpenTelemetry\Tracing\Sampler\AlwaysOnSampler;
use PHPUnit\Framework\TestCase;
class AlwaysOnTest extends TestCase
{
    public function testAlwaysOnSampler()
    {
        $sampler = new AlwaysOnSampler();
        $this->assertTrue($sampler->shouldSample());
    }
}
