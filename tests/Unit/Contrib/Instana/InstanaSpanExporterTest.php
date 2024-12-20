<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Contrib\Instana;

use OpenTelemetry\Contrib\Instana\SpanExporter as InstanaSpanExporter;
use OpenTelemetry\Contrib\Instana\SpanConverter as InstanaSpanConverter;

use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\Tests\Unit\SDK\Trace\SpanExporter\AbstractExporterTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(InstanaSpanExporter::class)]
class InstanaSpanExporterTest extends AbstractExporterTestCase
{
    /**
     * @psalm-suppress PossiblyInvalidArgument
     */
    public function createExporterWithTransport(TransportInterface $transport): InstanaSpanExporter
    {
        return new InstanaSpanExporter(
            $transport, new InstanaSpanConverter('0123456abcdef', '12345')
        );
    }

    public function getExporterClass(): string
    {
        return InstanaSpanExporter::class;
    }
}
