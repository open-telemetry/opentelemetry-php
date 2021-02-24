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
        int $traceId,
        int $spanId,
        string $spanName,
        int $spanKind,
        ?API\Attributes $attributes = null,
        ?API\Links $links = null
    ): SamplingResult {
        return new SamplingResult(SamplingResult::RECORD_AND_SAMPLED, $attributes, $links);
    }

    public function getDescription(): string
    {
        return 'AlwaysOnSampler';
    }
}
