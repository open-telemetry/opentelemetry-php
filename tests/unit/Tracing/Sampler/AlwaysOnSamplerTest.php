<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Tracing\Sampler;

use OpenTelemetry\Sdk\Trace\AlwaysOnSampler;
use PHPUnit\Framework\TestCase;

class AlwaysOnTest extends TestCase
{
    public function testAlwaysOnSampler()
    {
        $sampler = new AlwaysOnSampler();
        $this->assertTrue($sampler->shouldSample());
    }
}
