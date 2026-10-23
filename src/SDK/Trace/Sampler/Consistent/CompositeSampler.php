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
 * Makes the final sampling decision for a {@see ComposableSamplerInterface} and maintains the `ot` entry of the
 * TraceState.
 *
 * The decision compares the threshold of the intent with the randomness: `rv` or the last 56 bits of the trace ID
 * when the adjusted count is reliable, a fresh random value otherwise. The `th` sub-key is written only for sampled
 * spans with a reliable adjusted count and is erased otherwise; `rv` is never modified.
 *
 * @experimental
 *
 * @see https://opentelemetry.io/docs/specs/otel/trace/sdk/#compositesampler
 */
final class CompositeSampler implements SamplerInterface
{
    public function __construct(
        private readonly ComposableSamplerInterface $delegate,
    ) {
    }

    #[\Override]
    public function shouldSample(
        ContextInterface $parentContext,
        string $traceId,
        string $spanName,
        int $spanKind,
        AttributesInterface $attributes,
        array $links,
    ): SamplingResult {
        $parentTraceState = Span::fromContext($parentContext)->getContext()->getTraceState();
        $otelTraceState = OtelTraceState::parse($parentTraceState?->get(OtelTraceState::TRACE_STATE_KEY));

        $intent = $this->delegate->getSamplingIntent($parentContext, $spanName, $spanKind, $attributes, $links);
        $threshold = $intent->getThreshold();

        $isSampled = false;
        $isAdjustedCountReliable = false;

        if (ConsistentSamplingUtil::isValidThreshold($threshold)) {
            $isAdjustedCountReliable = $intent->isAdjustedCountReliable();
            $randomness = $isAdjustedCountReliable
                ? ConsistentSamplingUtil::getRandomnessValue($otelTraceState, $traceId)
                : random_int(0, ConsistentSamplingUtil::MAX_RANDOM_VALUE);
            $isSampled = $threshold <= $randomness;
        }

        if ($isSampled && $isAdjustedCountReliable) {
            $otelTraceState->setThreshold($threshold);
        } else {
            $otelTraceState->invalidateThreshold();
        }

        $newTraceState = $intent->updateTraceState($parentTraceState ?? new TraceState(), $isSampled);
        $serializedOt = $otelTraceState->serialize();
        $newTraceState = $serializedOt !== ''
            ? self::withOtEntry($newTraceState, $serializedOt)
            : $newTraceState->without(OtelTraceState::TRACE_STATE_KEY);

        return new SamplingResult(
            $isSampled ? SamplingResult::RECORD_AND_SAMPLE : SamplingResult::DROP,
            $isSampled ? $intent->getAttributes() : [],
            $newTraceState,
        );
    }

    #[\Override]
    public function getDescription(): string
    {
        return $this->delegate->getDescription();
    }

    /**
     * Sets the `ot` entry. When the TraceState is full the rightmost member is evicted first, as W3C Trace Context
     * requires when adding an entry; otherwise {@see TraceState::with()} would refuse the entry and lose the threshold.
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
