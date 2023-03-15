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

    public function emergency($message, array $context = [])
    {
    }

    public function alert($message, array $context = [])
    {
    }

    public function critical($message, array $context = [])
    {
    }

    public function error($message, array $context = [])
    {
    }

    public function warning($message, array $context = [])
    {
    }

    public function notice($message, array $context = [])
    {
    }

    public function info($message, array $context = [])
    {
    }

    public function debug($message, array $context = [])
    {
    }

    public function log($level, $message, array $context = [])
    {
    }
}
