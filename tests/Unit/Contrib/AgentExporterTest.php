<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Contrib\Unit;

use OpenTelemetry\Contrib\Jaeger\AgentExporter;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\Tests\Unit\SDK\Util\SpanData;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\Contrib\Jaeger\AgentExporter
 * @covers OpenTelemetry\Contrib\Jaeger\JaegerTransport
 * @covers OpenTelemetry\Contrib\Jaeger\ThriftUdpTransport
 */
class AgentExporterTest extends TestCase
{
    public function test_happy_path()
    {
        $exporter = AgentExporter::fromConnectionString(
            'http://127.0.0.1:80',
            'someServiceName',
        );

        $status = $exporter->export([new SpanData()]);

        $this->assertSame(SpanExporterInterface::STATUS_SUCCESS, $status);

        $exporter->closeAgentConnection();
    }
}
