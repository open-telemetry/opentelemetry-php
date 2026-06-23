<?php

declare(strict_types=1);

namespace OpenTelemetry\Test\Unit\Config\SDK\ComponentProvider\Trace;

use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\Config\SDK\ComponentProvider\Trace\SamplerTraceIdRatioBased;
use OpenTelemetry\SDK\Trace\Sampler\TraceIdRatioBasedSampler;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

#[CoversClass(SamplerTraceIdRatioBased::class)]
final class SamplerTraceIdRatioBasedTest extends TestCase
{
    public function test_get_config(): void
    {
        $provider = new SamplerTraceIdRatioBased();
        $registry = $this->createMock(ComponentProviderRegistry::class);
        $config = $provider->getConfig($registry, new NodeBuilder());
        $this->assertInstanceOf(ArrayNodeDefinition::class, $config);
    }

    public function test_create_plugin(): void
    {
        $provider = new SamplerTraceIdRatioBased();
        $sampler = $provider->createPlugin(['ratio' => 0.5], new Context());
        $this->assertInstanceOf(TraceIdRatioBasedSampler::class, $sampler);
    }
}
