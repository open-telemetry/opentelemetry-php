<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace\Sampler;

use OpenTelemetry\Sdk\Trace\Sampler;
use OpenTelemetry\Sdk\Trace\SamplingResult;
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
        int $traceId,
        int $spanId,
        string $spanName,
        int $spanKind,
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
