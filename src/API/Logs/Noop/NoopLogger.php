<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Logs\Noop;

use OpenTelemetry\API\Logs\LoggerInterface;
use OpenTelemetry\API\Logs\LogRecord;
use Psr\Log\LoggerTrait;

class NoopLogger implements LoggerInterface
{
    use LoggerTrait;

    public static function getInstance(): self
    {
        static $instance;

        return $instance ??= new self();
    }

    /**
     * @codeCoverageIgnore
     */
    public function emit(LogRecord $logRecord): void
    {
    }

    /**
     * @codeCoverageIgnore
     */
    public function log($level, $message, array $context = []): void
    {
    }
}
