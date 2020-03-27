<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace\Sampling;

use OpenTelemetry\Trace as API;

/**
 * This implementation of the SamplerInterface always skips record.
 * Example:
 * ```
 * use OpenTelemetry\Sdk\Trace\Sampler\AlwaysOffSampler;
 * $sampler = new AlwaysOffSampler();
 * ```
 */
class AlwaysOffSampler implements Sampler
{
    /**
     * Returns false because we never want to sample.
     * {@inheritdoc}
     */
    public function shouldSample(
        ?API\SpanContext $parentContext,
        string $traceId,
        string $spanId,
        string $spanName,
        // API\SpanKind $spanKind,
        array $attributes = [],
        array $links = []
    ): SamplingResult {
        return new SamplingResult(SamplingResult::NOT_RECORD);
    }

    public function getDescription(): string
    {
        return 'AlwaysOffSampler';
    }
}
