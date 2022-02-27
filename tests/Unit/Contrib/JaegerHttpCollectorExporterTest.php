<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Contrib\Unit;

use OpenTelemetry\Contrib\Jaeger\HttpCollectorExporter;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\Tests\Unit\SDK\Util\SpanData;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\Contrib\Jaeger\HttpCollectorExporter
 * @covers OpenTelemetry\Contrib\Jaeger\ThriftHttpSender
 */
class JaegerHttpCollectorExporterTest extends TestCase
{
    public function test_happy_path()
    {
        $exporter = HttpCollectorExporter::fromConnectionString(
            'https://httpbin.org/post', //FYI this will actually end up making a network request
            'someServiceName',
        );

        $status = $exporter->export([new SpanData()]);

        $this->assertSame(SpanExporterInterface::STATUS_SUCCESS, $status);
    }
}
