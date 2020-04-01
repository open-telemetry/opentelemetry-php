<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use OpenTelemetry\Trace as API;

final class SamplingResult
{
    /**
     * Span will not be recorded and all events and attributes will be dropped
     */
    public const NOT_RECORD = 0;

    /**
     * Span will be recorded but SpanExporters will not receive this Span
     */
    public const RECORD = 1;

    /**
     * Span will be recorder and exported.
     */
    public const RECORD_AND_SAMPLED = 2;

    /**
     * @var int A sampling Decision.
     */
    private $decision;

    /**
     * @var ?API\Attributes A set of span Attributes that will also be added to the Span.
     */
    private $attributes;

    public function __construct(int $decision, ?API\Attributes $attributes = null)
    {
        $this->decision = $decision;
        $this->attributes = $attributes;
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
    public function getAttributes(): ?API\Attributes
    {
        return $this->attributes;
    }
}
