<?php

declare(strict_types=1);

namespace OpenTelemetry\Test\Unit\Config\SDK\ComponentProvider\Logs;

use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\Config\SDK\ComponentProvider\Logs\LogRecordExporterConsole;
use OpenTelemetry\SDK\Common\Export\Stream\StreamTransportFactory;
use OpenTelemetry\SDK\Logs\Exporter\ConsoleExporter;
use OpenTelemetry\SDK\Registry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

#[CoversClass(LogRecordExporterConsole::class)]
final class LogRecordExporterConsoleTest extends TestCase
{
    public function test_get_config(): void
    {
        $provider = new LogRecordExporterConsole();
        $registry = $this->createMock(ComponentProviderRegistry::class);
        $config = $provider->getConfig($registry, new NodeBuilder());
        $this->assertInstanceOf(ArrayNodeDefinition::class, $config);
    }

    public function test_create_plugin(): void
    {
        Registry::registerTransportFactory('stream', new StreamTransportFactory());
        $provider = new LogRecordExporterConsole();
        $exporter = $provider->createPlugin([], new Context());
        $this->assertInstanceOf(ConsoleExporter::class, $exporter);
    }
}
