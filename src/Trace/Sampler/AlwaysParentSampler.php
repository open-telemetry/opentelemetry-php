<?php

declare(strict_types=1);

namespace OpenTelemetry\Trace\Sampler;

use OpenTelemetry\Context\SpanContext;

/**
 * This implementation of the SamplerInterface always records.
 * Example:
 * ```
 * use OpenTelemetry\Trace\Sampler\AlwaysParentSampler;
 * $sampler = new AlwaysParentSampler();
 * ```
 */
class AlwaysParentSampler implements Sampler
{
    /**
     * Returns `RECORD_AND_SAMPLED` if SampledFlag is set to true on parent SpanContext and `NOT_RECORD` otherwise.
     * {@inheritdoc}
     */
    public function shouldSample(
        ?SpanContext $parentContext,
        string $traceId,
        string $spanId,
        string $spanName,
        // SpanKind $spanKind,
        array $attributes = [],
        array $links = []
    ): SamplingResult {
        if (null !== $parentContext && $parentContext->isSampled()) {
            return new SamplingResult(SamplingResult::RECORD_AND_SAMPLED);
        }

        return new SamplingResult(SamplingResult::NOT_RECORD);
    }

    public function getDescription(): string
    {
        return 'AlwaysParentSampler';
    }
}
