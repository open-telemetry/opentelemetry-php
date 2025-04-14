<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs\Exporter;

use OpenTelemetry\SDK\Common\Services\Loader;
use OpenTelemetry\SDK\Logs\LogRecordExporterFactoryInterface;
use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;

class ConsoleExporterFactory implements LogRecordExporterFactoryInterface
{
    public function create(): LogRecordExporterInterface
    {
        $transport = Loader::transportFactory('stream')->create('php://stdout', 'application/json');

        return new ConsoleExporter($transport);
    }

    public function type(): string
    {
        return 'console';
    }

    public function priority(): int
    {
        return 0;
    }
}
