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

    public function emit(string $name, mixed $body = null, ?int $timestamp = null, ?ContextInterface $context = null, Severity|int|null $severityNumber = null, iterable $attributes = []): void
    {
    }
}
