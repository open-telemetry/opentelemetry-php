<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Contrib\Unit;

use OpenTelemetry\Contrib\Jaeger\AgentExporter;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\Tests\SDK\Util\SpanData;
use PHPUnit\Framework\TestCase;

class AgentExporterTest extends TestCase
{
    public function test_happy_path() 
    {
        $exporter = AgentExporter::fromConnectionString(
            "http://127.0.0.1:80", //This isn't realistic I imagine
            "serviceName", //This isn't realistic I imagine
        );

        $status = $exporter->export([new SpanData()]);

        $this->assertSame(SpanExporterInterface::STATUS_SUCCESS, $status);

        $exporter->jaegerTransport->close();
    }
}