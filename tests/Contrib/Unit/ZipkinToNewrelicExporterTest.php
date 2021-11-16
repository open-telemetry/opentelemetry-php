<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Contrib\Unit;

use OpenTelemetry\Contrib\ZipkinToNewrelic\Exporter;

class ZipkinToNewrelicExporterTest extends AbstractHttpExporterTest
{
    protected const EXPORTER_NAME = 'test.zipkinToNR';
    protected const LICENSE_KEY = 'abc123';

    public function createExporterWithDsn(string $dsn): Exporter
    {
        return new Exporter(
            self::EXPORTER_NAME,
            $dsn,
            self::LICENSE_KEY,
            $this->getClientInterfaceMock(),
            $this->getRequestFactoryInterfaceMock(),
            $this->getStreamFactoryInterfaceMock()
        );
    }
}
