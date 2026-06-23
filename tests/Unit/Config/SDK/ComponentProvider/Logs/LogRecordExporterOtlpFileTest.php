<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Config\SDK\ComponentProvider\Logs;

use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\Config\SDK\ComponentProvider\Logs\LogRecordExporterOtlpFile;
use OpenTelemetry\SDK\Common\Export\Stream\StreamTransportFactory;
use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;
use OpenTelemetry\SDK\Registry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

#[CoversClass(LogRecordExporterOtlpFile::class)]
final class LogRecordExporterOtlpFileTest extends TestCase
{
    public function test_get_config(): void
    {
        $provider = new LogRecordExporterOtlpFile();
        $registry = $this->createMock(ComponentProviderRegistry::class);
        $config = $provider->getConfig($registry, new NodeBuilder());
        $this->assertInstanceOf(ArrayNodeDefinition::class, $config);
    }

    public function test_create_plugin(): void
    {
        Registry::registerTransportFactory('stream', new StreamTransportFactory());
        $provider = new LogRecordExporterOtlpFile();
        $exporter = $provider->createPlugin(['output_stream' => 'stdout'], new Context());
        $this->assertInstanceOf(LogRecordExporterInterface::class, $exporter);
    }
}
