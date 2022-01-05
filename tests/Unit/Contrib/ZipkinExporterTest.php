<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Contrib;

use OpenTelemetry\Contrib\Zipkin\Exporter;

class ZipkinExporterTest extends AbstractHttpExporterTest
{
    protected const EXPORTER_NAME = 'test.zipkin';

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
