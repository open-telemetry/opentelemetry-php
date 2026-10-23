<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Sampler\Consistent;

use Closure;
use OpenTelemetry\API\Trace\TraceStateInterface;

/**
 * Immutable {@see SamplingIntentInterface}.
 *
 * @experimental
 */
final class SamplingIntent implements SamplingIntentInterface
{
    /** The threshold of an intent that drops the span. */
    public const INVALID_THRESHOLD = -1;

    /**
     * @param iterable<string, mixed> $attributes
     * @param (Closure(TraceStateInterface, bool): TraceStateInterface)|null $traceStateUpdater Must not modify the `ot` entry
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
