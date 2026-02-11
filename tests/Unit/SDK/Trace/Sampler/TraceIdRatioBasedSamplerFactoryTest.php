<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace\Sampler;

use OpenTelemetry\SDK\Trace\Sampler\TraceIdRatioBasedSampler;
use OpenTelemetry\SDK\Trace\Sampler\TraceIdRatioBasedSamplerFactory;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TraceIdRatioBasedSamplerFactory::class)]
class TraceIdRatioBasedSamplerFactoryTest extends TestCase
{
    use TestState;

    public function test_create(): void
    {
        $this->setEnvironmentVariable('OTEL_TRACES_SAMPLER_ARG', '0.5');
        $factory = new TraceIdRatioBasedSamplerFactory();
        $sampler = $factory->create();

        $this->assertInstanceOf(TraceIdRatioBasedSampler::class, $sampler);
        $this->assertStringContainsString('0.5', $sampler->getDescription());
    }
}
