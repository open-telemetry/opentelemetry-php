<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Contrib\Jaeger;

use OpenTelemetry\Contrib\Jaeger\Exporter;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\Tests\Unit\SDK\Trace\SpanExporter\AbstractExporterTest;

/**
 * @covers OpenTelemetry\Contrib\Jaeger\Exporter
 */
class JaegerExporterTest extends AbstractExporterTest
{
    public function createExporterWithTransport(TransportInterface $transport): Exporter
    {
        return new Exporter(
            $transport
        );
    }

    public function getExporterClass(): string
    {
        return Exporter::class;
    }
}
