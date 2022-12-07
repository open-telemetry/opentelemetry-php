<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Contrib\Jaeger;

use OpenTelemetry\Contrib\Jaeger\HttpCollectorExporter;
use OpenTelemetry\Tests\Unit\Contrib\UsesHttpClientTrait;
use OpenTelemetry\Tests\Unit\SDK\Util\SpanData;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\Contrib\Jaeger\HttpCollectorExporter
 * @covers \OpenTelemetry\Contrib\Jaeger\HttpSender
 * @covers \OpenTelemetry\Contrib\Jaeger\ThriftHttpTransport
 * @covers \OpenTelemetry\Contrib\Jaeger\ParsedEndpointUrl
 * @covers \OpenTelemetry\Contrib\Jaeger\BatchAdapter\BatchAdapter
 * @covers \OpenTelemetry\Contrib\Jaeger\BatchAdapter\BatchAdapterFactory
 *
 */
class JaegerHttpCollectorExporterTest extends TestCase
{
    use UsesHttpClientTrait;

    public function test_happy_path()
    {
        /**
         * @psalm-suppress PossiblyInvalidArgument
         */
        $exporter = new HttpCollectorExporter(
            'https://hostOfJaegerCollector.com/post',
            $this->getClientInterfaceMock(),
            $this->getRequestFactoryInterfaceMock(),
            $this->getStreamFactoryInterfaceMock()
        );

        $status = $exporter->export([new SpanData()])->await();

        $this->assertTrue($status);
    }
}
