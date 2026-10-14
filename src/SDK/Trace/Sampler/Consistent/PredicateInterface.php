<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Sampler\Consistent;

use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Trace\LinkInterface;

/**
 * A predicate used by {@see ConsistentRuleBasedSampler} to match spans.
 *
 * @experimental
 */
interface PredicateInterface
{
    /**
     * Returns true if the span described by the given arguments matches this predicate.
     *
     * @param list<LinkInterface> $links
     */
    public function spanMatches(
        ContextInterface $parentContext,
        string $spanName,
        int $spanKind,
        AttributesInterface $attributes,
        array $links,
    ): bool;
}
