<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Contrib\Zipkin;

use OpenTelemetry\Contrib\Zipkin\Exporter;

use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\Tests\Unit\SDK\Trace\SpanExporter\AbstractExporterTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\OpenTelemetry\Contrib\Zipkin\Exporter::class)]
class ZipkinExporterTest extends AbstractExporterTestCase
{
    /**
     * @psalm-suppress PossiblyInvalidArgument
     */
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
