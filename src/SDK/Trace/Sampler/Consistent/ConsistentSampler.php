<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Sampler\Consistent;

use function end;
use function explode;
use OpenTelemetry\API\Trace\TraceState;
use OpenTelemetry\API\Trace\TraceStateInterface;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use OpenTelemetry\SDK\Trace\SamplingResult;
use OpenTelemetry\SDK\Trace\Span;
use function random_int;
use function strstr;

/**
 * Abstract base class for consistent probability samplers.
 *
 * Subclasses implement {@see ComposableInterface::getSamplingIntent()} to express their preferred
 * threshold; this class handles the final sampling decision and TraceState updates.
 *
 * @experimental
 *
 * @see https://opentelemetry.io/docs/specs/otel/trace/tracestate-probability-sampling/
 */
abstract class ConsistentSampler implements SamplerInterface, ComposableInterface
{
    // ─── Static factory methods ────────────────────────────────────────────────

    /** Returns a sampler that samples every span (threshold = 0). */
    public static function alwaysOn(): self
    {
        return ConsistentAlwaysOnSampler::getInstance();
    }

    /** Returns a sampler that drops every span. */
    public static function alwaysOff(): self
    {
        return ConsistentAlwaysOffSampler::getInstance();
    }

    /**
     * Returns a sampler that samples each span with a fixed probability.
     *
     * A probability of 0 is non-probabilistic, so {@see self::alwaysOff()} is returned for it.
     *
     * @param float $samplingProbability Probability in [0, 1]
     */
    public static function probabilityBased(float $samplingProbability): self
    {
        $threshold = ConsistentSamplingUtil::calculateThreshold($samplingProbability);
        if ($samplingProbability === 0.0) {
            return self::alwaysOff();
        }

        return new ConsistentFixedThresholdSampler($threshold);
    }

    /**
     * Returns a sampler that limits the rate of sampled spans of $delegate to about $targetSpansPerSecondLimit.
     *
     * Stateful, so it only works in long-running PHP processes; it does NOT work with PHP-FPM or Apache.
     * See {@see ConsistentRateLimitingSampler}.
     *
     * @param float $targetSpansPerSecondLimit Desired maximum of sampled spans per second
     * @param float $adaptationTimeSeconds Typical time to adapt to a new load
     */
    public static function rateLimited(ComposableInterface $delegate, float $targetSpansPerSecondLimit, float $adaptationTimeSeconds): self
    {
        return new ConsistentRateLimitingSampler($delegate, $targetSpansPerSecondLimit, $adaptationTimeSeconds);
    }

    /**
     * Returns a sampler (CompositeSampler) that makes the final decision for any composable sampler.
     */
    public static function composite(ComposableInterface $delegate): self
    {
        return new ConsistentCompositeSampler($delegate);
    }

    /**
     * Returns a sampler that delegates the decision to $delegate and adds $attributes to sampled spans.
     *
     * @param array<non-empty-string, mixed> $attributes Attributes to add to sampled spans
     */
    public static function annotating(array $attributes, ComposableInterface $delegate): self
    {
        return new ConsistentAnnotatingSampler($attributes, $delegate);
    }

    /**
     * Returns a sampler that propagates the parent's threshold and falls back to $root for root spans.
     */
    public static function parentBased(ComposableInterface $root): self
    {
        return new ConsistentParentBasedSampler($root);
    }

    /**
     * Returns a rule-based sampler that evaluates $samplers in order.
     *
     * @param int|null $spanKindToMatch Restricts to a specific SpanKind; null matches all kinds
     * @param PredicatedSampler ...$samplers (predicate, delegate) pairs to evaluate
     */
    public static function ruleBased(?int $spanKindToMatch, PredicatedSampler ...$samplers): self
    {
        return new ConsistentRuleBasedSampler($spanKindToMatch, ...$samplers);
    }

    /**
     * Returns a sampler that samples a span when ANY of the delegates would sample it.
     *
     * The combined threshold is the minimum of all valid delegate thresholds.
     *
     * @param ComposableInterface ...$delegates At least one delegate is required
     */
    public static function anyOf(ComposableInterface ...$delegates): self
    {
        return new ConsistentAnyOf(...$delegates);
    }

    // ─── Core shouldSample() ───────────────────────────────────────────────────

    /**
     * Final implementation of the SamplerInterface contract.
     *
     * Algorithm (per spec):
     * 1. Parse parent OtelTraceState.
     * 2. Invoke getSamplingIntent() on the concrete subclass.
     * 3. threshold == INVALID → DROP.
     * 4. Derive R: if reliable=true, from rv (if present), else from last 56 bits of traceId;
     *    if !reliable → fresh random 56-bit value, even when rv is present (non-consistent decision,
     *    as the threshold may depend on the trace randomness).
     * 5. sampled = (threshold <= R).
     * 6. If sampled && reliable → write th to OtelTraceState; else erase th.
     *    rv is never modified.
     * 7. Build SamplingResult with the updated TraceState.
     */
    #[\Override]
    final public function shouldSample(
        ContextInterface $parentContext,
        string $traceId,
        string $spanName,
        int $spanKind,
        AttributesInterface $attributes,
        array $links,
    ): SamplingResult {
        $parentSpan = Span::fromContext($parentContext);
        $parentSpanContext = $parentSpan->getContext();
        $parentTraceState = $parentSpanContext->getTraceState();

        // Parse OtelTraceState from the `ot` key of the parent TraceState
        $otStr = $parentTraceState?->get(OtelTraceState::TRACE_STATE_KEY);
        $otelTraceState = OtelTraceState::parse($otStr);

        // ── Ask the concrete subclass for its intent ───────────────────────────
        $intent = $this->getSamplingIntent($parentContext, $spanName, $spanKind, $attributes, $links);
        $threshold = $intent->getThreshold();

        // ── Make the sampling decision ─────────────────────────────────────────
        $isSampled = false;
        $isAdjustedCountReliable = false;

        if (ConsistentSamplingUtil::isValidThreshold($threshold)) {
            $isAdjustedCountReliable = $intent->isAdjustedCountReliable();

            if ($isAdjustedCountReliable) {
                // Use rv (if present) or last 56 bits of the traceId
                $randomness = ConsistentSamplingUtil::getRandomnessValue($otelTraceState, $traceId);
            } else {
                // !reliable → generate fresh random R (non-consistent decision)
                $randomness = random_int(0, ConsistentSamplingUtil::MAX_RANDOM_VALUE);
            }

            $isSampled = $threshold <= $randomness;
        }

        // ── Update OtelTraceState (th only; rv is immutable) ──────────────────
        if ($isSampled && $isAdjustedCountReliable) {
            $otelTraceState->setThreshold($threshold);
        } else {
            $otelTraceState->invalidateThreshold();
        }

        // ── Build the output TraceState ────────────────────────────────────────
        $serializedOt = $otelTraceState->serialize();
        // Let the intent update any non-ot keys; it is told the final decision and may react to it.
        $newTraceState = $intent->updateTraceState($parentTraceState ?? new TraceState(), $isSampled);

        if ($serializedOt !== '') {
            $newTraceState = self::withOtEntry($newTraceState, $serializedOt);
        } else {
            $newTraceState = $newTraceState->without(OtelTraceState::TRACE_STATE_KEY);
        }

        // ── Assemble SamplingResult ────────────────────────────────────────────
        $decision = $isSampled ? SamplingResult::RECORD_AND_SAMPLE : SamplingResult::DROP;

        return new SamplingResult(
            $decision,
            $isSampled ? $intent->getAttributes() : [],
            $newTraceState,
        );
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Sets the `ot` entry, first evicting the rightmost (oldest) member when the TraceState is full
     * (at most {@see TraceState::MAX_LIST_MEMBERS} members), as W3C Trace Context requires when adding an entry.
     * Without this, {@see TraceState::with()} would silently refuse to add `ot` and the threshold would be lost.
     */
    private static function withOtEntry(TraceStateInterface $traceState, string $serializedOt): TraceStateInterface
    {
        if ($traceState->get(OtelTraceState::TRACE_STATE_KEY) === null
            && $traceState->getListMemberCount() >= TraceState::MAX_LIST_MEMBERS) {
            $members = explode(',', $traceState->toString());
            $rightmostKey = strstr((string) end($members), '=', true);
            if ($rightmostKey !== false && $rightmostKey !== '') {
                $traceState = $traceState->without($rightmostKey);
            }
        }

        return $traceState->with(OtelTraceState::TRACE_STATE_KEY, $serializedOt);
    }
}
