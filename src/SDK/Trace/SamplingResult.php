<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use OpenTelemetry\API\Trace as API;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;

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
     * @var int A sampling Decision.
     */
    private int $decision;

    /**
     * @var ?AttributesInterface A set of span Attributes that will also be added to the Span.
     */
    private ?AttributesInterface $attributes;

    /**
     * @var ?API\TraceStateInterface A Tracestate that will be associated with the Span through the new SpanContext.
     */
    private ?API\TraceStateInterface $traceState;

    public function __construct(int $decision, ?AttributesInterface $attributes = null, ?API\TraceStateInterface $traceState = null)
    {
        $this->decision = $decision;
        $this->attributes = $attributes;
        $this->traceState = $traceState;
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
    public function getAttributes(): ?AttributesInterface
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
