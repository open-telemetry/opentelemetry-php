<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Logs;

interface LoggerInterface
{
    public function emit(LogRecord $logRecord): void;
}
