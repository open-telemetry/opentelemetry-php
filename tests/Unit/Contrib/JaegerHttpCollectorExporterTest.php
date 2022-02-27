<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Contrib;

use OpenTelemetry\Contrib\Jaeger\HttpCollectorExporter;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\Tests\Unit\SDK\Util\SpanData;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\Contrib\Jaeger\HttpCollectorExporter
 * @covers OpenTelemetry\Contrib\Jaeger\ThriftHttpSender
 * @covers OpenTelemetry\Contrib\Jaeger\CustomizedTHttpClient
 */
class JaegerHttpCollectorExporterTest extends TestCase
{
    use UsesHttpClientTrait;

    public function test_happy_path()
    {
        $exporter = new HttpCollectorExporter(
            'https://hostOfJaegerCollector.com/post',
            'nameOfThisService',
            $this->getClientInterfaceMock(),
            $this->getRequestFactoryInterfaceMock(),
            $this->getStreamFactoryInterfaceMock()
        );

        $status = $exporter->export([new SpanData()]);

        $this->assertSame(SpanExporterInterface::STATUS_SUCCESS, $status);
    }
}
