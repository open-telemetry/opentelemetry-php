<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Tracing\Sampler;

use OpenTelemetry\Sdk\Trace\AlwaysOffSampler;
use PHPUnit\Framework\TestCase;

class AlwaysOffSamplerTest extends TestCase
{
    public function testAlwaysOffSampler()
    {
        $sampler = new AlwaysOffSampler();
        $this->assertFalse($sampler->shouldSample());
    }
}
