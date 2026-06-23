<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Config\SDK\ComponentProvider\Trace;

use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\Config\SDK\ComponentProvider\Trace\SpanExporterZipkin;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransportFactory;
use OpenTelemetry\SDK\Registry;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

#[CoversClass(SpanExporterZipkin::class)]
final class SpanExporterZipkinTest extends TestCase
{
    public function test_get_config(): void
    {
        $provider = new SpanExporterZipkin();
        $registry = $this->createMock(ComponentProviderRegistry::class);
        $config = $provider->getConfig($registry, new NodeBuilder());
        $this->assertInstanceOf(ArrayNodeDefinition::class, $config);
    }

    public function test_create_plugin(): void
    {
        Registry::registerTransportFactory('http', new PsrTransportFactory());
        $provider = new SpanExporterZipkin();
        $exporter = $provider->createPlugin([
            'endpoint' => 'http://localhost:9411/api/v2/spans',
            'timeout' => 10000,
        ], new Context());
        $this->assertInstanceOf(SpanExporterInterface::class, $exporter);
    }
}
