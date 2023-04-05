<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs\Exporter;

use OpenTelemetry\SDK\Logs\LogRecordExporterFactoryInterface;
use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;

class InMemoryExporterFactory implements LogRecordExporterFactoryInterface
{
    public function create(): LogRecordExporterInterface
    {
        return new InMemoryExporter();
    }
}
