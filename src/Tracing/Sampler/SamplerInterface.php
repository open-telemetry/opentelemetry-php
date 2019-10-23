<?php
namespace OpenTelemetry\Tracing\Sampler;
/**
 * This interface is used to organize sampling logic.
 */
interface SamplerInterface
{
    /**
     * Returns true if we should sample the request.
     *
     * @return bool
     */
    public function shouldSample();
}