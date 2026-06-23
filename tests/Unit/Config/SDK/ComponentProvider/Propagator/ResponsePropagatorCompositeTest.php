<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Config\SDK\ComponentProvider\Propagator;

use OpenTelemetry\API\Configuration\Config\ComponentPlugin;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\Config\SDK\ComponentProvider\Propagator\ResponsePropagatorComposite;
use OpenTelemetry\Context\Propagation\MultiResponsePropagator;
use OpenTelemetry\Context\Propagation\ResponsePropagatorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

#[CoversClass(ResponsePropagatorComposite::class)]
final class ResponsePropagatorCompositeTest extends TestCase
{
    public function test_get_config(): void
    {
        $provider = new ResponsePropagatorComposite();
        $registry = $this->createMock(ComponentProviderRegistry::class);
        $registry->method('componentNames')
            ->with('composite', ResponsePropagatorInterface::class)
            ->willReturn(new ArrayNodeDefinition('composite'));
        $config = $provider->getConfig($registry, new NodeBuilder());
        $this->assertInstanceOf(ArrayNodeDefinition::class, $config);
    }

    public function test_create_plugin_empty(): void
    {
        $provider = new ResponsePropagatorComposite();
        $result = $provider->createPlugin([], new Context());
        $this->assertInstanceOf(MultiResponsePropagator::class, $result);
    }

    public function test_create_plugin_with_propagators(): void
    {
        $mockPropagator = $this->createMock(ResponsePropagatorInterface::class);
        $plugin = $this->createMock(ComponentPlugin::class);
        $plugin->method('create')->willReturn($mockPropagator);

        $provider = new ResponsePropagatorComposite();
        $result = $provider->createPlugin([$plugin], new Context());
        $this->assertInstanceOf(MultiResponsePropagator::class, $result);
    }

    public function test_create_plugin_with_multiple_propagators(): void
    {
        $mockPropagator1 = $this->createMock(ResponsePropagatorInterface::class);
        $plugin1 = $this->createMock(ComponentPlugin::class);
        $plugin1->method('create')->willReturn($mockPropagator1);

        $mockPropagator2 = $this->createMock(ResponsePropagatorInterface::class);
        $plugin2 = $this->createMock(ComponentPlugin::class);
        $plugin2->method('create')->willReturn($mockPropagator2);

        $provider = new ResponsePropagatorComposite();
        $result = $provider->createPlugin([$plugin1, $plugin2], new Context());
        $this->assertInstanceOf(MultiResponsePropagator::class, $result);
    }
}
