<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Logs;

use OpenTelemetry\Context\ContextInterface;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/logs/event-api.md#events-api-interface
 */
interface EventLoggerInterface
{
    public function emit(
        string $name,
        mixed $payload = null,
        ?int $timestamp = null,
        ?ContextInterface $context = null,
        Severity|int|null $severityNumber = null,
        iterable $attributes = [],
    ): void;
}
