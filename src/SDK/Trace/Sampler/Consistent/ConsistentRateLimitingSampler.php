<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Sampler\Consistent;

use Closure;
use InvalidArgumentException;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;

/**
 * Consistent sampler that limits the rate of sampled spans by adaptively lowering the sampling probability
 * suggested by a delegate. Port of opentelemetry-java-contrib's `ConsistentRateLimitingSampler`.
 *
 * The sampler keeps a time-decayed estimate of the incoming span rate and of the delegate's average probability,
 * and reduces the probability when the estimated rate of sampled spans would exceed the target.
 *
 * WARNING: this sampler is stateful and the state lives in the PHP process only. It works in long-running PHP
 * processes (CLI workers, Swoole, RoadRunner, FrankenPHP worker mode, ReactPHP, ...). It does NOT work with
 * PHP-FPM or Apache (mod_php): every request starts with an empty window, so the observed time window is
 * (nearly) zero and almost all spans are dropped instead of being limited to the target rate. Do not use it there.
 *
 * Not part of the OpenTelemetry specification and not available in declarative configuration.
 *
 * @experimental
 */
final class ConsistentRateLimitingSampler extends ConsistentSampler
{
    private const NANOS_IN_SECOND = 1e-9;

    private readonly string $description;
    private readonly float $inverseAdaptationTimeNanos;
    private readonly float $targetSpansPerNanosecondLimit;
    private readonly float $probabilitySmoothingFactor;
    /** @var Closure(): int */
    private readonly Closure $nanoTime;

    private float $effectiveWindowCount = 0.0;
    private float $effectiveWindowNanos = 0.0;
    private int $lastNanoTime;
    private float $effectiveDelegateProbability = 1.0;

    /**
     * @param ComposableInterface $delegate Sampler whose probability is lowered when the limit would be exceeded
     * @param float $targetSpansPerSecondLimit Desired maximum of sampled spans per second, must be positive
     * @param float $adaptationTimeSeconds Typical time to adapt to a new load (time constant of the exponential smoothing), must be positive
     * @param (Closure(): int)|null $nanoTime Monotonic clock in nanoseconds, defaults to {@see hrtime()}
     */
    public function __construct(
        private readonly ComposableInterface $delegate,
        float $targetSpansPerSecondLimit,
        float $adaptationTimeSeconds,
        ?Closure $nanoTime = null,
    ) {
        if (is_nan($targetSpansPerSecondLimit) || $targetSpansPerSecondLimit <= 0.0) {
            throw new InvalidArgumentException('Limit for sampled spans per second must be positive.');
        }
        if (is_nan($adaptationTimeSeconds) || $adaptationTimeSeconds <= 0.0) {
            throw new InvalidArgumentException('Adaptation time must be positive.');
        }

        $this->description = sprintf(
            'ConsistentRateLimitingSampler{targetSpansPerSecondLimit=%s, adaptationTimeSeconds=%s}',
            $targetSpansPerSecondLimit,
            $adaptationTimeSeconds,
        );
        $this->nanoTime = $nanoTime ?? static fn (): int => hrtime(true);
        $this->inverseAdaptationTimeNanos = self::NANOS_IN_SECOND / $adaptationTimeSeconds;
        $this->targetSpansPerNanosecondLimit = self::NANOS_IN_SECOND * $targetSpansPerSecondLimit;
        $this->probabilitySmoothingFactor = self::determineProbabilitySmoothingFactor($targetSpansPerSecondLimit, $adaptationTimeSeconds);
        $this->lastNanoTime = ($this->nanoTime)();
    }

    #[\Override]
    public function getSamplingIntent(
        ContextInterface $parentContext,
        string $spanName,
        int $spanKind,
        AttributesInterface $attributes,
        array $links,
    ): SamplingIntentInterface {
        $delegateIntent = $this->delegate->getSamplingIntent($parentContext, $spanName, $spanKind, $attributes, $links);
        $delegateThreshold = $delegateIntent->getThreshold();

        if (ConsistentSamplingUtil::isValidThreshold($delegateThreshold)) {
            $delegateProbability = ConsistentSamplingUtil::calculateSamplingProbability($delegateThreshold);
            $this->updateState(($this->nanoTime)(), $delegateProbability);

            $targetMaxProbability = ($this->effectiveWindowNanos * $this->targetSpansPerNanosecondLimit) / $this->effectiveWindowCount;
            $suggestedProbability = $this->effectiveDelegateProbability > $targetMaxProbability
                ? $targetMaxProbability / $this->effectiveDelegateProbability * $delegateProbability
                : $delegateProbability;
            $suggestedThreshold = ConsistentSamplingUtil::calculateThreshold(min(1.0, max(0.0, $suggestedProbability)));
        } else {
            $suggestedThreshold = ConsistentSamplingUtil::INVALID_THRESHOLD;
        }

        return new SamplingIntent(
            threshold: $suggestedThreshold,
            adjustedCountReliable: $delegateIntent->isAdjustedCountReliable(),
            attributes: $delegateIntent->getAttributes(),
            traceStateUpdater: $delegateIntent->updateTraceState(...),
        );
    }

    #[\Override]
    public function getDescription(): string
    {
        return $this->description;
    }

    private function updateState(int $currentNanoTime, float $delegateProbability): void
    {
        $this->effectiveDelegateProbability = $this->effectiveDelegateProbability * (1.0 - $this->probabilitySmoothingFactor)
            + $delegateProbability * $this->probabilitySmoothingFactor;

        $nanoTimeDelta = $currentNanoTime - $this->lastNanoTime;
        if ($nanoTimeDelta <= 0) {
            // Low clock resolution or clock jumping backwards: assume a time delta of zero.
            $this->effectiveWindowCount += 1.0;

            return;
        }

        $decayFactor = exp(-$nanoTimeDelta * $this->inverseAdaptationTimeNanos);
        $this->effectiveWindowCount = $this->effectiveWindowCount * $decayFactor + 1.0;
        $this->effectiveWindowNanos = $this->effectiveWindowNanos * $decayFactor + $nanoTimeDelta;
        $this->lastNanoTime = $currentNanoTime;
    }

    private static function determineProbabilitySmoothingFactor(float $targetSpansPerSecondLimit, float $adaptationTimeSeconds): float
    {
        // The smoothing factor alpha is the weight of the newly observed probability; (1 - alpha) is the weight of the
        // cumulative average. It is inversely proportional to the adaptation time and to the expected span rate
        // (assumed to be of the order of the limit), and "bent" so that it always stays below 1.
        $t = 1.0 / ($targetSpansPerSecondLimit * $adaptationTimeSeconds);

        return $t / (1.0 + $t);
    }
}
