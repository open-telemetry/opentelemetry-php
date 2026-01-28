<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace\Sampler;

use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSamplerFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AlwaysOnSamplerFactory::class)]
class AlwaysOnSamplerFactoryTest extends TestCase
{
    public function test_create(): void
    {
        $factory = new AlwaysOnSamplerFactory();

        $this->assertInstanceOf(AlwaysOnSampler::class, $factory->create());
    }
}
