<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use OpenTelemetry\Context\SpanContext;

/**
 * This interface is used to organize sampling logic.
 *
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/master/specification/sdk-tracing.md#sampler
 */
interface Sampler
{
    /**
     * Returns SamplingResult.
     *
     * @param SpanContext|null $parentContext `SpanContext` of a parent Span. Typically extracted from the wire. Can be null.
     * @param string $traceId TraceId of the Span to be created. It can be different from the TraceId in the SpanContext.
     *                        Typically in situations when the Span to be created starts a new Trace.
     * @param string $spanId SpanId of the Span to be created.
     * @param string $spanName Name of the Span to be created.
     * @param array $attributes Initial set of Attributes for the Span being constructed.
     * @param array $links Collection of links that will be associated with the Span to be created.
     *                     Typically useful for batch operations.
     *                     @see https://github.com/open-telemetry/opentelemetry-specification/blob/master/specification/overview.md#links-between-spans
     * @return SamplingResult
     */
    public function shouldSample(
        ?SpanContext $parentContext,
        string $traceId,
        string $spanId,
        string $spanName,
        /* SpanKind $spanKind, */ // TODO: missing SpanKind class
        array $attributes = [],
        array $links = []
    ): SamplingResult;

    /**
     * Returns the sampler name or short description with the configuration.
     * This may be displayed on debug pages or in the logs.
     * Example: "ProbabilitySampler{0.000100}"
     * @return string
     */
    public function getDescription(): string;
}
