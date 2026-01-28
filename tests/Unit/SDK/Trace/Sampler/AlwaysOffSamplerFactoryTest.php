<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace\Sampler;

use OpenTelemetry\SDK\Trace\Sampler\AlwaysOffSampler;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOffSamplerFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AlwaysOffSamplerFactory::class)]
class AlwaysOffSamplerFactoryTest extends TestCase
{
    public function test_create(): void
    {
        $factory = new AlwaysOffSamplerFactory();

        $this->assertInstanceOf(AlwaysOffSampler::class, $factory->create());
    }
}
