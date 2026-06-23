<?php

declare(strict_types=1);

namespace OpenTelemetry\Test\Unit\Config\SDK\ComponentProvider\Propagator;

use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\Config\SDK\ComponentProvider\Propagator\TextMapPropagatorComposite;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

#[CoversClass(TextMapPropagatorComposite::class)]
final class TextMapPropagatorCompositeTest extends TestCase
{
    public function test_get_config(): void
    {
        $provider = new TextMapPropagatorComposite();
        $registry = $this->createMock(ComponentProviderRegistry::class);
        $registry->method('componentNames')
            ->with('composite', TextMapPropagatorInterface::class)
            ->willReturn(new ArrayNodeDefinition('composite'));
        $config = $provider->getConfig($registry, new NodeBuilder());
        $this->assertInstanceOf(ArrayNodeDefinition::class, $config);
    }
}
