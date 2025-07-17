<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs\Exporter;

use OpenTelemetry\SDK\Common\Export\InMemoryStorageManager;
use OpenTelemetry\SDK\Logs\LogRecordExporterFactoryInterface;
use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;

class InMemoryExporterFactory implements LogRecordExporterFactoryInterface
{
    #[\Override]
    public function create(): LogRecordExporterInterface
    {
        return new InMemoryExporter(InMemoryStorageManager::logs());
    }
}
