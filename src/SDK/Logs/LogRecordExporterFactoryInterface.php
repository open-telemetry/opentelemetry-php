<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs;

use OpenTelemetry\SDK\Common\Services\SpiLoadableInterface;

interface LogRecordExporterFactoryInterface extends SpiLoadableInterface
{
    public function create(): LogRecordExporterInterface;
}
