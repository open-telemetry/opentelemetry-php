<?php

declare(strict_types=1);

namespace OpenTelemetry\Test\Unit\Config\SDK\ComponentProvider\Propagator;

use OpenTelemetry\API\Baggage\Propagation\BaggagePropagator;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\Config\SDK\ComponentProvider\Propagator\TextMapPropagatorBaggage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

#[CoversClass(TextMapPropagatorBaggage::class)]
final class TextMapPropagatorBaggageTest extends TestCase
{
    public function test_get_config(): void
    {
        $provider = new TextMapPropagatorBaggage();
        $registry = $this->createMock(ComponentProviderRegistry::class);
        $config = $provider->getConfig($registry, new NodeBuilder());
        $this->assertInstanceOf(ArrayNodeDefinition::class, $config);
    }

    public function test_create_plugin(): void
    {
        $provider = new TextMapPropagatorBaggage();
        $propagator = $provider->createPlugin([], new Context());
        $this->assertInstanceOf(BaggagePropagator::class, $propagator);
    }
}
