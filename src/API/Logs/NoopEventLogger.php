<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Logs;

use OpenTelemetry\Context\ContextInterface;

class NoopEventLogger implements EventLoggerInterface
{
    public static function instance(): self
    {
        static $instance;
        $instance ??= new self();

        return $instance;
    }

    /**
     * @inheritDoc
     */
    public function logEvent(string $eventName, LogRecord $logRecord): void
    {
    }

    public function emit(string $name, mixed $payload = null, ?int $timestamp = null, ?ContextInterface $context = null, Severity|int|null $severityNumber = null, ?array $attributes = []): void
    {
    }
}
