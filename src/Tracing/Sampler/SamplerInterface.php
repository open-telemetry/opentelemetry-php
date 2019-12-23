<?php
namespace OpenTelemetry\Tracing\Sampler;

/**
 * This interface is used to organize sampling logic.
 */
interface SamplerInterface
{
    const FLAG_SAMPLED = 1;

    /**
     * Returns true if we should sample the request.
     *
     * @return bool
     */
    public function shouldSample(): bool;

    /**
     * Returns the sampler name or short description with the configuration.
     * This may be displayed on debug pages or in the logs.
     * Example: "ProbabilitySampler{0.000100}"
     * @return string
     */
    public function getDescription(): string;
} 
