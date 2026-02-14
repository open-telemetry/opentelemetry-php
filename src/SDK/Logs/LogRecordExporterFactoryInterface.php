<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs;

/**
 * TODO deprecated
 */
interface LogRecordExporterFactoryInterface
{
    public function create(): LogRecordExporterInterface;
}
