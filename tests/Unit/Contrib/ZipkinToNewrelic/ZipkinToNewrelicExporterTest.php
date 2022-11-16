<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Contrib\ZipkinToNewrelic;

use OpenTelemetry\Contrib\ZipkinToNewrelic\Exporter;

use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\Tests\Unit\SDK\Trace\SpanExporter\AbstractExporterTest;

/**
 * @covers OpenTelemetry\Contrib\ZipkinToNewrelic\Exporter
 */
class ZipkinToNewrelicExporterTest extends AbstractExporterTest
{
    protected const EXPORTER_NAME = 'test.zipkinToNR';
    protected const LICENSE_KEY = 'abc123';

    /**
     * @psalm-suppress PossiblyInvalidArgument
     */
    public function createExporterWithTransport(TransportInterface $transport): Exporter
    {
        return new Exporter(
            self::EXPORTER_NAME,
            $transport
        );
    }

    public function getExporterClass(): string
    {
        return Exporter::class;
    }
}
