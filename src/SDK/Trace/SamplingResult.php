<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use OpenTelemetry\API\Trace as API;

final class SamplingResult
{
    /**
     * Span will not be recorded and all events and attributes will be dropped.
     */
    public const DROP = 0;

    /**
     * Span will be recorded but SpanExporters will not receive this Span.
     */
    public const RECORD_ONLY = 1;

    /**
     * Span will be recorder and exported.
     */
    public const RECORD_AND_SAMPLE = 2;

    /**
     * @param ?API\TraceStateInterface $traceState A Tracestate that will be associated with the Span through the new SpanContext.
     */
    public function __construct(
        private readonly int $decision,
        private readonly iterable $attributes = [],
        private readonly ?API\TraceStateInterface $traceState = null,
    ) {
    }

    /**
     * Return sampling decision whether span should be recorded or not.
     */
    public function getDecision(): int
    {
        return $this->decision;
    }

    /**
     * Return attributes which will be attached to the span.
     */
    public function getAttributes(): iterable
    {
        return $this->attributes;
    }

    /**
     * Return a collection of links that will be associated with the Span to be created.
     */
    public function getTraceState(): ?API\TraceStateInterface
    {
        return $this->traceState;
    }
}
