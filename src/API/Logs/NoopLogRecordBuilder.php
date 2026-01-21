<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Logs;

use OpenTelemetry\Context\ContextInterface;
use Throwable;

/**
 * @psalm-internal OpenTelemetry
 */
final class NoopLogRecordBuilder implements LogRecordBuilderInterface
{
    #[\Override]
    public function setTimestamp(int $timestamp): LogRecordBuilderInterface
    {
        return $this;
    }

    #[\Override]
    public function setObservedTimestamp(int $timestamp): LogRecordBuilderInterface
    {
        return $this;
    }

    #[\Override]
    public function setContext(ContextInterface|false|null $context): LogRecordBuilderInterface
    {
        return $this;
    }

    #[\Override]
    public function setSeverityNumber(int|Severity $severityNumber): LogRecordBuilderInterface
    {
        return $this;
    }

    #[\Override]
    public function setSeverityText(string $severityText): LogRecordBuilderInterface
    {
        return $this;
    }

    #[\Override]
    public function setBody(mixed $body): LogRecordBuilderInterface
    {
        return $this;
    }

    #[\Override]
    public function setAttribute(string $key, mixed $value): LogRecordBuilderInterface
    {
        return $this;
    }

    #[\Override]
    public function setAttributes(iterable $attributes): LogRecordBuilderInterface
    {
        return $this;
    }

    #[\Override]
    public function setException(Throwable $exception): LogRecordBuilderInterface
    {
        return $this;
    }

    #[\Override]
    public function setEventName(string $eventName): LogRecordBuilderInterface
    {
        return $this;
    }

    #[\Override]
    public function emit(): void
    {
        // no-op
    }
}
