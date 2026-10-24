<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Sampler\Consistent;

use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Trace\LinkInterface;

/**
 * A sampler that returns its sampling preference (a {@see SamplingIntentInterface}) instead of a decision.
 * The final decision is made by the {@see CompositeSampler} that wraps it.
 *
 * The threshold of an intent must not depend on the randomness value or on the sampled flag of the parent,
 * because both are derived from the same random source and conditioning on them would bias the statistics.
 * {@see ComposableParentThreshold} is the exception: it falls back to the sampled flag of a parent without `th`.
 *
 * @experimental
 */
interface ComposableSamplerInterface
{
    /**
     * @param list<LinkInterface> $links
     */
    public function getSamplingIntent(
        ContextInterface $parentContext,
        string $spanName,
        int $spanKind,
        AttributesInterface $attributes,
        array $links,
    ): SamplingIntentInterface;

    public function getDescription(): string;
}
