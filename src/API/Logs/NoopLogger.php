<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Logs;

use OpenTelemetry\Context\ContextInterface;
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

    public function isEnabled(?ContextInterface $context = null, ?int $severityNumber = null): bool
    {
        return false;
    }
}
