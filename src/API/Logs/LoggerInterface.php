<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Logs;

use Psr\Log\LoggerInterface as Psr3LoggerInterface;

interface LoggerInterface extends Psr3LoggerInterface
{
    public function logRecord(LogRecord $logRecord): void;
}
