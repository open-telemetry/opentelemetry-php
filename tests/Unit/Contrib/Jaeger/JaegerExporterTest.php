<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Contrib\Jaeger;

use OpenTelemetry\Contrib\Jaeger\Exporter;

/**
 * @covers OpenTelemetry\Contrib\Jaeger\Exporter
 */
class JaegerExporterTest extends AbstractHttpExporterTest
{
    use UsesHttpClientTrait;

    private const EXPORTER_NAME = 'test.jaeger';

    /**
     * @psalm-suppress PossiblyInvalidArgument
     */
    public function createExporterWithDsn(string $dsn): Exporter
    {
        return new Exporter(
            self::EXPORTER_NAME,
            $dsn,
            $this->getClientInterfaceMock(),
            $this->getRequestFactoryInterfaceMock(),
            $this->getStreamFactoryInterfaceMock()
        );
    }

    public function getExporterClass(): string
    {
        return Exporter::class;
    }
}
