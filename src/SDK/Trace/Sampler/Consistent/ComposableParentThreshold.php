<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Sampler\Consistent;

use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Trace\Span;

/**
 * Propagates the sampling decision of the parent and delegates root spans to the root sampler.
 *
 * - A valid parent `th` that is consistent with the sampled flag of the parent (sampled == R >= th) is propagated
 *   with a reliable adjusted count.
 * - An apparently inconsistent `th` is erased, that is treated as absent.
 * - Without a usable `th`, a sampled parent gives threshold 0 and an unsampled parent drops the span,
 *   both with an unreliable adjusted count.
 *
 * @experimental
 */
final class ComposableParentThreshold implements ComposableSamplerInterface
{
    private readonly string $description;

    public function __construct(
        private readonly ComposableSamplerInterface $rootSampler,
    ) {
        $this->description = 'ComposableParentThreshold{root=' . $rootSampler->getDescription() . '}';
    }

    #[\Override]
    public function getSamplingIntent(
        ContextInterface $parentContext,
        string $spanName,
        int $spanKind,
        AttributesInterface $attributes,
        array $links,
    ): SamplingIntentInterface {
        $parentSpanContext = Span::fromContext($parentContext)->getContext();

        if (!$parentSpanContext->isValid()) {
            return $this->rootSampler->getSamplingIntent($parentContext, $spanName, $spanKind, $attributes, $links);
        }

        $otelTraceState = OtelTraceState::parse($parentSpanContext->getTraceState()?->get(OtelTraceState::TRACE_STATE_KEY));

        if ($otelTraceState->hasValidThreshold()) {
            $parentThreshold = $otelTraceState->getThreshold();
            // A child shares the trace ID of its parent, so R is the one used for the parent's decision
            $randomness = ConsistentSamplingUtil::getRandomnessValue($otelTraceState, $parentSpanContext->getTraceId());

            if ($parentSpanContext->isSampled() === ($parentThreshold <= $randomness)) {
                return new SamplingIntent(threshold: $parentThreshold, adjustedCountReliable: true);
            }
        }

        return new SamplingIntent(
            threshold: $parentSpanContext->isSampled()
                ? ConsistentSamplingUtil::MIN_THRESHOLD
                : SamplingIntent::INVALID_THRESHOLD,
            adjustedCountReliable: false,
        );
    }

    #[\Override]
    public function getDescription(): string
    {
        return $this->description;
    }
}
