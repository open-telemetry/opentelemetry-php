<?php
namespace OpenTelemetry\Trace\Sampler;

/**
 * This implementation of the SamplerInterface always returns false.
 * Example:
 * ```
 * use OpenTelemetry\Traceing\Sampler\NeverSampleSampler;
 * $sampler = new NeverSampleSampler();
 * ```
 */
class AlwaysOffSampler implements SamplerInterface
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
