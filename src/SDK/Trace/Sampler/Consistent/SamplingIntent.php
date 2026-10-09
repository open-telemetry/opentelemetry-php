<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Sampler\Consistent;

use Closure;
use OpenTelemetry\API\Trace\TraceStateInterface;

/**
 * Immutable value object implementing {@see SamplingIntentInterface}.
 *
 * @experimental
 */
final class SamplingIntent implements SamplingIntentInterface
{
    /**
     * @param int $threshold Rejection threshold; {@see ConsistentSamplingUtil::INVALID_THRESHOLD} = drop
     * @param bool $adjustedCountReliable Whether the threshold can be used for adjusted count
     * @param iterable<string, mixed> $attributes Attributes to add when sampled
     * @param Closure(TraceStateInterface, bool): TraceStateInterface|null $traceStateUpdater Function to modify the parent TraceState, given the final sampling decision (must not touch the `ot` key)
     */
    public function __construct(
        private readonly int $threshold,
        private readonly bool $adjustedCountReliable = true,
        private readonly iterable $attributes = [],
        private readonly ?Closure $traceStateUpdater = null,
    ) {
    }

    #[\Override]
    public function getThreshold(): int
    {
        return $this->threshold;
    }

    #[\Override]
    public function isAdjustedCountReliable(): bool
    {
        return $this->adjustedCountReliable;
    }

    #[\Override]
    public function getAttributes(): iterable
    {
        return $this->attributes;
    }

    #[\Override]
    public function updateTraceState(TraceStateInterface $parentState, bool $sampled): TraceStateInterface
    {
        if ($this->traceStateUpdater !== null) {
            return ($this->traceStateUpdater)($parentState, $sampled);
        }

        return $parentState;
    }
}
