<?php
namespace OpenTelemetry\Tracing\Sampler;


/**
 * This implementation of the SamplerInterface always returns false.
 * Example:
 * ```
 * use OpenTelemetry\Traceing\Sampler\NeverSampleSampler;
 * $sampler = new NeverSampleSampler();
 * ```
 */
class NeverSampleSampler implements SamplerInterface
{
    /**
     * Returns false because we never want to sample.
     *
     * @return bool
     */
    public function shouldSample()
    {
        return false;
    }
}