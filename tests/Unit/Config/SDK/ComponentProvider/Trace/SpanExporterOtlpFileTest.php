<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Config\SDK\ComponentProvider\Trace;

use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\Config\SDK\ComponentProvider\Trace\SpanExporterOtlpFile;
use OpenTelemetry\SDK\Common\Export\Stream\StreamTransportFactory;
use OpenTelemetry\SDK\Registry;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

#[CoversClass(SpanExporterOtlpFile::class)]
final class SpanExporterOtlpFileTest extends TestCase
{
    public function test_get_config(): void
    {
        $provider = new SpanExporterOtlpFile();
        $registry = $this->createMock(ComponentProviderRegistry::class);
        $config = $provider->getConfig($registry, new NodeBuilder());
        $this->assertInstanceOf(ArrayNodeDefinition::class, $config);
    }

    public function test_create_plugin(): void
    {
        Registry::registerTransportFactory('stream', new StreamTransportFactory());
        $provider = new SpanExporterOtlpFile();
        $exporter = $provider->createPlugin(['output_stream' => 'stdout'], new Context());
        $this->assertInstanceOf(SpanExporterInterface::class, $exporter);
    }
}
