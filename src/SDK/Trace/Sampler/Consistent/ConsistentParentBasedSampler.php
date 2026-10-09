<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Sampler\Consistent;

use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Trace\LinkInterface;
use OpenTelemetry\SDK\Trace\Span;

/**
 * Consistent sampler that propagates the parent's threshold for child spans and delegates to
 * $root for root spans.
 *
 * For child spans:
 * - If the parent OtelTraceState contains a valid `th` that is consistent with the parent's sampled flag
 *   (sampled == R >= th) → propagate that threshold (reliable=true).
 * - If `th` is apparently inconsistent with the sampled flag, it is erased, i.e. treated as absent.
 * - If no valid `th` and the parent is sampled → use threshold 0 (reliable=false, so th is erased).
 * - If no valid `th` and the parent is not sampled → INVALID_THRESHOLD (DROP, reliable=false).
 *
 * @experimental
 */
class ConsistentParentBasedSampler extends ConsistentSampler
{
    private readonly string $description;

    public function __construct(
        private readonly ComposableInterface $rootSampler,
    ) {
        $this->description = 'ConsistentParentBasedSampler{rootSampler=' . $rootSampler->getDescription() . '}';
    }

    #[\Override]
    public function getSamplingIntent(
        ContextInterface $parentContext,
        string $spanName,
        int $spanKind,
        AttributesInterface $attributes,
        array $links,
    ): SamplingIntentInterface {
        $parentSpan = Span::fromContext($parentContext);
        $parentSpanContext = $parentSpan->getContext();

        // Root span — delegate to the root sampler
        if (!$parentSpanContext->isValid()) {
            return $this->rootSampler->getSamplingIntent($parentContext, $spanName, $spanKind, $attributes, $links);
        }

        $parentTraceState = $parentSpanContext->getTraceState();
        $otStr = $parentTraceState?->get(OtelTraceState::TRACE_STATE_KEY);
        $otelTraceState = OtelTraceState::parse($otStr);

        if ($otelTraceState->hasValidThreshold()) {
            $parentThreshold = $otelTraceState->getThreshold();
            // A child shares the parent's trace ID, so R is the same as for the parent's decision
            $randomness = ConsistentSamplingUtil::getRandomnessValue($otelTraceState, $parentSpanContext->getTraceId());

            // Spec: the sampled flag must match `R >= th`, otherwise the threshold is apparently
            // inconsistent and is erased (falls through to the "no threshold" handling below).
            if ($parentSpanContext->isSampled() === ($parentThreshold <= $randomness)) {
                // Propagate the parent's threshold; it is reliable because we know exactly what it is
                return new SamplingIntent(
                    threshold: $parentThreshold,
                    adjustedCountReliable: true,
                    attributes: $this->getAttributesForParentSpanContext($parentSpanContext->isRemote(), $spanName, $spanKind, $attributes, $links),
                );
            }
        }

        // No (usable) parent threshold — fall back to sampled flag
        if ($parentSpanContext->isSampled()) {
            // Propagate the sampling decision (sampled=true), but we cannot know the original
            // probability, so reliable=false (th will be erased in ConsistentSampler::shouldSample)
            $parentThreshold = ConsistentSamplingUtil::MIN_THRESHOLD;
        } else {
            $parentThreshold = ConsistentSamplingUtil::INVALID_THRESHOLD;
        }

        return new SamplingIntent(
            threshold: $parentThreshold,
            adjustedCountReliable: false,
            attributes: $this->getAttributesForParentSpanContext($parentSpanContext->isRemote(), $spanName, $spanKind, $attributes, $links),
        );
    }

    // ─── Hooks (override in subclasses to add attributes) ─────────────────────

    /**
     * Returns attributes to add when the parent span is local (in-process).
     *
     * @param list<LinkInterface> $links
     * @return iterable<string, mixed>
     */
    protected function getAttributesWhenParentLocal(
        string $spanName,
        int $spanKind,
        AttributesInterface $attributes,
        array $links,
    ): iterable {
        return [];
    }

    /**
     * Returns attributes to add when the parent span is remote.
     *
     * @param list<LinkInterface> $links
     * @return iterable<string, mixed>
     */
    protected function getAttributesWhenParentRemote(
        string $spanName,
        int $spanKind,
        AttributesInterface $attributes,
        array $links,
    ): iterable {
        return [];
    }

    #[\Override]
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param list<LinkInterface> $links
     * @return iterable<string, mixed>
     */
    private function getAttributesForParentSpanContext(
        bool $isRemote,
        string $spanName,
        int $spanKind,
        AttributesInterface $attributes,
        array $links,
    ): iterable {
        if ($isRemote) {
            return $this->getAttributesWhenParentRemote($spanName, $spanKind, $attributes, $links);
        }

        return $this->getAttributesWhenParentLocal($spanName, $spanKind, $attributes, $links);
    }
}
