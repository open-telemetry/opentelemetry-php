<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;

/**
 * This interface is used to organize sampling logic.
 *
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/trace/sdk.md#sampler
 */
interface SamplerInterface
{
    /**
     * Returns SamplingResult.
     *
     * @param ContextInterface $parentContext Context with parent Span. The Span's SpanContext may be invalid to indicate a root span.
     * @param string $traceId TraceId of the Span to be created. It can be different from the TraceId in the SpanContext.
     *                        Typically in situations when the Span to be created starts a new Trace.
     * @param string $spanName Name of the Span to be created.
     * @param int $spanKind Span kind.
     * @param AttributesInterface $attributes Initial set of Attributes for the Span being constructed.
     * @param list<LinkInterface> $links Collection of links that will be associated with the Span to be created.
     *                     Typically, useful for batch operations.
     *                     @see https://github.com/open-telemetry/opentelemetry-specification/blob/master/specification/overview.md#links-between-spans
     * @return SamplingResult
     */
    public function shouldSample(
        ContextInterface $parentContext,
        string $traceId,
        string $spanName,
        int $spanKind,
        AttributesInterface $attributes,
        array $links
    ): SamplingResult;

    /**
     * Returns the sampler name or short description with the configuration.
     * This may be displayed on debug pages or in the logs.
     * Example: "TraceIdRatioBasedSampler{0.000100}"
     */
    public function getDescription(): string;
}
