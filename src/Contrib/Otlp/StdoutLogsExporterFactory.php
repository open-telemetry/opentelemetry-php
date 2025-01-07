<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Otlp;

use OpenTelemetry\SDK\Common\Export\Stream\StreamTransportFactory;
use OpenTelemetry\SDK\Logs\LogRecordExporterFactoryInterface;
use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;

class StdoutLogsExporterFactory implements LogRecordExporterFactoryInterface
{
    public function create(): LogRecordExporterInterface
    {
        $transport = (new StreamTransportFactory())->create('php://stdout', ContentTypes::NDJSON);

        return new LogsExporter($transport);
    }
}
