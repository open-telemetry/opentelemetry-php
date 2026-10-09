<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Sampler\Consistent;

use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Trace\LinkInterface;

/**
 * Interface for composable consistent probability samplers.
 *
 * Implementations return a {@see SamplingIntentInterface} indicating their preferred threshold
 * for the span, without making the final decision (that is done by {@see ConsistentSampler}).
 *
 * IMPORTANT: The returned threshold MUST NOT depend — directly or indirectly — on the
 * randomness value or the sampled flag of the parent, as those are derived from the same
 * random source. Conditioning on them would bias the sampling statistics.
 * The only exception is {@see ConsistentParentBasedSampler}, which falls back to the parent's sampled
 * flag when the parent carries no valid `th` (the resulting adjusted count is then not reliable).
 *
 * @experimental
 */
interface ComposableInterface
{
    /**
     * Returns the sampling intent for the span being created.
     *
     * @param ContextInterface $parentContext Context of the parent span
     * @param string $spanName Name of the span being created
     * @param int $spanKind SpanKind constant
     * @param AttributesInterface $attributes Initial attributes of the span
     * @param list<LinkInterface> $links Links associated with the span
     */
    public function getSamplingIntent(
        ContextInterface $parentContext,
        string $spanName,
        int $spanKind,
        AttributesInterface $attributes,
        array $links,
    ): SamplingIntentInterface;

    /**
     * Returns a human-readable description of this sampler (used for debug pages / logging).
     */
    public function getDescription(): string;
}
