<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

/**
 * This implementation of the SamplerInterface always returns true.
 * Example:
 * ```
 * use OpenTelemetry\Sdk\Trace\AlwaysSampleSampler;
 * $sampler = new AlwaysSampleSampler();
 * ```
 */
class AlwaysOnSampler implements Sampler
{
    /**
     * Returns true because we always want to sample.
     *
     * @return bool
     */
    public function shouldSample(): bool
    {
        return true;
    }

    public function getDescription(): string
    {
        return self::class;
    }
}
