<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Sampler\Consistent;

use OpenTelemetry\API\Trace\TraceStateInterface;

/**
 * The sampling preference returned by a {@see ComposableSamplerInterface}.
 *
 * @experimental
 */
interface SamplingIntentInterface
{
    /**
     * The rejection threshold: the lower, the more likely the span is sampled (0 samples every span).
     * {@see SamplingIntent::INVALID_THRESHOLD} means that the span is dropped.
     */
    public function getThreshold(): int;

    /**
     * Whether the threshold can be used to compute the adjusted count (the reciprocal of the sampling probability,
     * used for span to metrics estimation).
     */
    public function isAdjustedCountReliable(): bool;

    /**
     * Attributes to add to the span when it is sampled.
     *
     * @return iterable<string, mixed>
     */
    public function getAttributes(): iterable;

    /**
     * Returns the TraceState of the span, given the one of the parent and the final decision. Called for every span.
     * Implementations must not modify the `ot` entry, which is owned by the {@see CompositeSampler}.
     *
     * @param bool $sampled whether the final decision is RECORD_AND_SAMPLE
     */
    public function updateTraceState(TraceStateInterface $parentState, bool $sampled): TraceStateInterface;
}
