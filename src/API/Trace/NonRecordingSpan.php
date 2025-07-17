<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace;

use Throwable;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.6.1/specification/trace/api.md#wrapping-a-spancontext-in-a-span
 *
 * @psalm-internal OpenTelemetry
 */
final class NonRecordingSpan extends Span
{
    public function __construct(private readonly SpanContextInterface $context)
    {
    }

    /** @inheritDoc */
    #[\Override]
    public function getContext(): SpanContextInterface
    {
        return $this->context;
    }

    /** @inheritDoc */
    #[\Override]
    public function isRecording(): bool
    {
        return false;
    }

    /** @inheritDoc */
    #[\Override]
    public function setAttribute(string $key, $value): SpanInterface
    {
        return $this;
    }

    /** @inheritDoc */
    #[\Override]
    public function setAttributes(iterable $attributes): SpanInterface
    {
        return $this;
    }

    #[\Override]
    public function addLink(SpanContextInterface $context, iterable $attributes = []): SpanInterface
    {
        return $this;
    }

    /** @inheritDoc */
    #[\Override]
    public function addEvent(string $name, iterable $attributes = [], ?int $timestamp = null): SpanInterface
    {
        return $this;
    }

    /** @inheritDoc */
    #[\Override]
    public function recordException(Throwable $exception, iterable $attributes = []): SpanInterface
    {
        return $this;
    }

    /** @inheritDoc */
    #[\Override]
    public function updateName(string $name): SpanInterface
    {
        return $this;
    }

    /** @inheritDoc */
    #[\Override]
    public function setStatus(string $code, ?string $description = null): SpanInterface
    {
        return $this;
    }

    /** @inheritDoc */
    #[\Override]
    public function end(?int $endEpochNanos = null): void
    {
    }
}
