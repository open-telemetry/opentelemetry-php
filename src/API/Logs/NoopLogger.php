<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Logs;

class NoopLogger implements LoggerInterface
{
    public static function getInstance(): self
    {
        static $instance;

        return $instance ??= new self();
    }

    public function logRecord(LogRecord $logRecord): void
    {
        //do nothing
    }
}
