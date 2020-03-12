<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

/**
 * This implementation of the SamplerInterface always returns false.
 * Example:
 * ```
 * use OpenTelemetry\Sdk\Trace\NeverSampleSampler;
 * $sampler = new NeverSampleSampler();
 * ```
 */
class AlwaysOffSampler implements Sampler
{
    /**
     * Returns false because we never want to sample.
     *
     * @return bool
     */
    public function shouldSample(): bool
    {
        return false;
    }

    public function getDescription(): string
    {
        return self::class;
    }
}
