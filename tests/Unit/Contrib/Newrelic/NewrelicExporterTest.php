<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Contrib\Newrelic;

use OpenTelemetry\Contrib\Newrelic\Exporter;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\Tests\Unit\SDK\Trace\SpanExporter\AbstractExporterTest;

/**
 * @covers OpenTelemetry\Contrib\Newrelic\Exporter
 */
class NewrelicExporterTest extends AbstractExporterTest
{
    protected const LICENSE_KEY = 'abc123';

    public function createExporterWithTransport(TransportInterface $transport): Exporter
    {
        return new Exporter(
            $transport,
            'http://endpoint.url'
        );
    }

    public function getExporterClass(): string
    {
        return Exporter::class;
    }
}
