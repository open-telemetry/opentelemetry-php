<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace\Sampler;

use OpenTelemetry\Sdk\Trace\Sampler;
use OpenTelemetry\Sdk\Trace\SamplingResult;
use OpenTelemetry\Trace as API;

/**
 * This implementation of the SamplerInterface always records.
 * Example:
 * ```
 * use OpenTelemetry\Trace\ParentBased;
 * $sampler = new ParentBased();
 * ```
 */
class ParentBased implements Sampler
{
    /**
     * Returns `RECORD_AND_SAMPLED` if SampledFlag is set to true on parent SpanContext and `NOT_RECORD` otherwise.
     * {@inheritdoc}
     */
    public function shouldSample(
        ?API\SpanContext $parentContext,
        string $traceId,
        string $spanId,
        string $spanName,
        int $spanKind,
        ?API\Attributes $attributes = null,
        ?API\Links $links = null
    ): SamplingResult {
        if (null !== $parentContext && ($parentContext->getTraceFlags() & API\SpanContext::TRACE_FLAG_SAMPLED)) {
            return new SamplingResult(SamplingResult::RECORD_AND_SAMPLED, $attributes, $links);
        }

        return new SamplingResult(SamplingResult::NOT_RECORD, $attributes, $links);
    }

    public function getDescription(): string
    {
        return 'ParentBased';
    }
}
