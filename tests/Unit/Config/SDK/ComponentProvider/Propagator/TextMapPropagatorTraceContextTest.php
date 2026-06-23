<?php

declare(strict_types=1);

namespace OpenTelemetry\Test\Unit\Config\SDK\ComponentProvider\Propagator;

use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;
use OpenTelemetry\Config\SDK\ComponentProvider\Propagator\TextMapPropagatorTraceContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

#[CoversClass(TextMapPropagatorTraceContext::class)]
final class TextMapPropagatorTraceContextTest extends TestCase
{
    public function test_get_config(): void
    {
        $provider = new TextMapPropagatorTraceContext();
        $registry = $this->createMock(ComponentProviderRegistry::class);
        $config = $provider->getConfig($registry, new NodeBuilder());
        $this->assertInstanceOf(ArrayNodeDefinition::class, $config);
    }

    public function test_create_plugin(): void
    {
        $provider = new TextMapPropagatorTraceContext();
        $propagator = $provider->createPlugin([], new Context());
        $this->assertInstanceOf(TraceContextPropagator::class, $propagator);
    }
}
