<?php

declare(strict_types=1);

namespace OpenTelemetry\Test\Unit\Config\SDK\ComponentProvider\Metrics;

use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\Config\SDK\ComponentProvider\Metrics\MetricExporterConsole;
use OpenTelemetry\SDK\Metrics\MetricExporter\ConsoleMetricExporter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

#[CoversClass(MetricExporterConsole::class)]
final class MetricExporterConsoleTest extends TestCase
{
    public function test_get_config(): void
    {
        $provider = new MetricExporterConsole();
        $registry = $this->createMock(ComponentProviderRegistry::class);
        $config = $provider->getConfig($registry, new NodeBuilder());
        $this->assertInstanceOf(ArrayNodeDefinition::class, $config);
    }

    public function test_create_plugin(): void
    {
        $provider = new MetricExporterConsole();
        $exporter = $provider->createPlugin([], new Context());
        $this->assertInstanceOf(ConsoleMetricExporter::class, $exporter);
    }
}
