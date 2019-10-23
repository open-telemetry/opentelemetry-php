<?php
namespace OpenTelemetry\Tracing\Sampler;


/**
 * This implementation of the SamplerInterface always returns true.
 * Example:
 * ```
 * use OpenTelemetry\Traceing\Sampler\AlwaysSampleSampler;
 * $sampler = new AlwaysSampleSampler();
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