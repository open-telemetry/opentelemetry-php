<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Config\SDK\ComponentProvider\Metrics;

use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\Config\SDK\ComponentProvider\Metrics\MetricExporterOtlpFile;
use OpenTelemetry\SDK\Common\Export\Stream\StreamTransportFactory;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;
use OpenTelemetry\SDK\Registry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

#[CoversClass(MetricExporterOtlpFile::class)]
final class MetricExporterOtlpFileTest extends TestCase
{
    public function test_get_config(): void
    {
        $provider = new MetricExporterOtlpFile();
        $registry = $this->createMock(ComponentProviderRegistry::class);
        $config = $provider->getConfig($registry, new NodeBuilder());
        $this->assertInstanceOf(ArrayNodeDefinition::class, $config);
    }

    public function test_create_plugin_cumulative(): void
    {
        Registry::registerTransportFactory('stream', new StreamTransportFactory());
        $provider = new MetricExporterOtlpFile();
        $exporter = $provider->createPlugin([
            'output_stream' => 'stdout',
            'temporality_preference' => 'cumulative',
            'default_histogram_aggregation' => 'explicit_bucket_histogram',
        ], new Context());
        $this->assertInstanceOf(MetricExporterInterface::class, $exporter);
    }

    public function test_create_plugin_delta(): void
    {
        Registry::registerTransportFactory('stream', new StreamTransportFactory());
        $provider = new MetricExporterOtlpFile();
        $exporter = $provider->createPlugin([
            'output_stream' => 'stdout',
            'temporality_preference' => 'delta',
            'default_histogram_aggregation' => 'explicit_bucket_histogram',
        ], new Context());
        $this->assertInstanceOf(MetricExporterInterface::class, $exporter);
    }

    public function test_create_plugin_lowmemory(): void
    {
        Registry::registerTransportFactory('stream', new StreamTransportFactory());
        $provider = new MetricExporterOtlpFile();
        $exporter = $provider->createPlugin([
            'output_stream' => 'stdout',
            'temporality_preference' => 'lowmemory',
            'default_histogram_aggregation' => 'explicit_bucket_histogram',
        ], new Context());
        $this->assertInstanceOf(MetricExporterInterface::class, $exporter);
    }
}
