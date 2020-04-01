<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use OpenTelemetry\Trace as API;

/**
 * This implementation of the SamplerInterface always records.
 * Example:
 * ```
 * use OpenTelemetry\Sdk\Trace\AlwaysOnSampler;
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
        ?API\SpanContext $parentContext,
        string $traceId,
        string $spanId,
        string $spanName,
        // API\SpanKind $spanKind,
        ?API\Attributes $attributes = null,
        ?API\Links $links = null
    ): SamplingResult {
        // todo: hook up $attributes and $links
        return new SamplingResult(SamplingResult::RECORD_AND_SAMPLED);
    }

    public function getDescription(): string
    {
        return 'AlwaysOnSampler';
    }
}
