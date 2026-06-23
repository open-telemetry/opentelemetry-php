<?php

declare(strict_types=1);

namespace OpenTelemetry\Test\Unit\Config\SDK\ComponentProvider\Logs;

use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\Config\SDK\ComponentProvider\Logs\LogRecordExporterMemory;
use OpenTelemetry\SDK\Logs\Exporter\InMemoryExporter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

#[CoversClass(LogRecordExporterMemory::class)]
final class LogRecordExporterMemoryTest extends TestCase
{
    public function test_get_config(): void
    {
        $provider = new LogRecordExporterMemory();
        $registry = $this->createMock(ComponentProviderRegistry::class);
        $config = $provider->getConfig($registry, new NodeBuilder());
        $this->assertInstanceOf(ArrayNodeDefinition::class, $config);
    }

    public function test_create_plugin(): void
    {
        $provider = new LogRecordExporterMemory();
        $exporter = $provider->createPlugin([], new Context());
        $this->assertInstanceOf(InMemoryExporter::class, $exporter);
    }
}
