<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Sampler\Consistent;

use OpenTelemetry\API\Trace\TraceStateInterface;

/**
 * Represents a sampling decision intent returned by a {@see ComposableInterface}.
 *
 * @experimental
 */
interface SamplingIntentInterface
{
    /**
     * Returns the suggested rejection threshold.
     *
     * A lower value increases the likelihood of sampling (0 = always sample).
     * {@see ConsistentSamplingUtil::INVALID_THRESHOLD} means "drop".
     */
    public function getThreshold(): int;

    /**
     * Returns true if the threshold value can be reliably used to compute the adjusted count
     * (the reciprocal of the sampling probability, used for Span-to-Metrics estimation).
     */
    public function isAdjustedCountReliable(): bool;

    /**
     * Returns the attributes to add to the span when the sampling decision is positive.
     *
     * @return iterable<string, mixed>
     */
    public function getAttributes(): iterable;

    /**
     * Given the parent TraceState and the final sampling decision, returns the (possibly updated)
     * TraceState to attach to the span. Called for every span, sampled or not.
     *
     * Note: implementations MUST NOT modify the OpenTelemetry `ot` sub-key; that is the sole
     * responsibility of {@see ConsistentSampler}.
     *
     * @param bool $sampled true if the final decision is RECORD_AND_SAMPLE, false if it is DROP
     */
    public function updateTraceState(TraceStateInterface $parentState, bool $sampled): TraceStateInterface;
}
