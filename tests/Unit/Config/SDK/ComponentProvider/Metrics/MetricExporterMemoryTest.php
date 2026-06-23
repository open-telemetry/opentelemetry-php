<?php

declare(strict_types=1);

namespace OpenTelemetry\Test\Unit\Config\SDK\ComponentProvider\Metrics;

use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\Config\SDK\ComponentProvider\Metrics\MetricExporterMemory;
use OpenTelemetry\SDK\Metrics\MetricExporter\InMemoryExporter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

#[CoversClass(MetricExporterMemory::class)]
final class MetricExporterMemoryTest extends TestCase
{
    public function test_get_config(): void
    {
        $provider = new MetricExporterMemory();
        $registry = $this->createMock(ComponentProviderRegistry::class);
        $config = $provider->getConfig($registry, new NodeBuilder());
        $this->assertInstanceOf(ArrayNodeDefinition::class, $config);
    }

    public function test_create_plugin(): void
    {
        $provider = new MetricExporterMemory();
        $exporter = $provider->createPlugin([], new Context());
        $this->assertInstanceOf(InMemoryExporter::class, $exporter);
    }
}
