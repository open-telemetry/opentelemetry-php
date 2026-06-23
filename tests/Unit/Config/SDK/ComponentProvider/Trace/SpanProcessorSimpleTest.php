<?php

declare(strict_types=1);

namespace OpenTelemetry\Test\Unit\Config\SDK\ComponentProvider\Trace;

use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\Config\SDK\ComponentProvider\Trace\SpanProcessorSimple;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

#[CoversClass(SpanProcessorSimple::class)]
final class SpanProcessorSimpleTest extends TestCase
{
    public function test_get_config(): void
    {
        $provider = new SpanProcessorSimple();
        $registry = $this->createMock(ComponentProviderRegistry::class);
        $nodeDefinition = $this->createMock(NodeDefinition::class);
        $nodeDefinition->method('isRequired')->willReturnSelf();
        $registry->method('component')
            ->with('exporter', SpanExporterInterface::class)
            ->willReturn($nodeDefinition);
        $config = $provider->getConfig($registry, new NodeBuilder());
        $this->assertInstanceOf(ArrayNodeDefinition::class, $config);
    }
}
