<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Trace as API;

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
     * @param Context $parentContext Context with parent Span. The Span's SpanContext may be invalid to indicate a root span.
     * @param string $traceId TraceId of the Span to be created. It can be different from the TraceId in the SpanContext.
     *                        Typically in situations when the Span to be created starts a new Trace.
     * @param string $spanName Name of the Span to be created.
     * @param int $spanKind Span kind.
     * @param API\Attributes|null $attributes Initial set of Attributes for the Span being constructed.
     * @param API\Links|null $links Collection of links that will be associated with the Span to be created.
     *                     Typically useful for batch operations.
     *                     @see https://github.com/open-telemetry/opentelemetry-specification/blob/master/specification/overview.md#links-between-spans
     * @return SamplingResult
     */
    public function shouldSample(
        Context $parentContext,
        string $traceId,
        string $spanName,
        int $spanKind,
        ?API\Attributes $attributes = null,
        ?API\Links $links = null
    ): SamplingResult;

    /**
     * Returns the sampler name or short description with the configuration.
     * This may be displayed on debug pages or in the logs.
     * Example: "TraceIdRatioBasedSampler{0.000100}"
     * @return string
     */
    public function getDescription(): string;
}
