<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace\Sampler;

use OpenTelemetry\SDK\Trace\Sampler\ParentBased;
use OpenTelemetry\SDK\Trace\Sampler\ParentBasedTraceIdRatioSamplerFactory;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ParentBasedTraceIdRatioSamplerFactory::class)]
class ParentBasedTraceIdRatioSamplerFactoryTest extends TestCase
{
    use TestState;

    public function test_create(): void
    {
        $this->setEnvironmentVariable('OTEL_TRACES_SAMPLER_ARG', '0.5');
        $factory = new ParentBasedTraceIdRatioSamplerFactory();
        $sampler = $factory->create();

        $this->assertInstanceOf(ParentBased::class, $sampler);
        $this->assertStringContainsString('ParentBased', $sampler->getDescription());
        $this->assertStringContainsString('TraceIdRatio', $sampler->getDescription());
        $this->assertStringContainsString('0.5', $sampler->getDescription());
    }
}
