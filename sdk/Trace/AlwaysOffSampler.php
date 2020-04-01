<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use OpenTelemetry\Trace as API;

/**
 * This implementation of the SamplerInterface always skips record.
 * Example:
 * ```
 * use OpenTelemetry\Sdk\Trace\AlwaysOffSampler;
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
        ?API\Attributes $attributes = null,
        ?API\Links $links = null
    ): SamplingResult {
        return new SamplingResult(SamplingResult::NOT_RECORD);
    }

    public function getDescription(): string
    {
        return 'AlwaysOffSampler';
    }
}
