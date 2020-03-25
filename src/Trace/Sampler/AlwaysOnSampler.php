<?php

declare(strict_types=1);

namespace OpenTelemetry\Trace\Sampler;

use OpenTelemetry\Context\SpanContext;

/**
 * This implementation of the SamplerInterface always records.
 * Example:
 * ```
 * use OpenTelemetry\Trace\Sampler\AlwaysOnSampler;
 * $sampler = new AlwaysOnSampler();
 * ```
 */
class AlwaysOnSampler implements Sampler
{
    /**
     * Returns true because we always want to sample.
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
        return new SamplingResult(SamplingResult::RECORD_AND_SAMPLED);
    }

    public function getDescription(): string
    {
        return 'AlwaysOnSampler';
    }
}
