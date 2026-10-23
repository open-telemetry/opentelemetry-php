<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Sampler\Consistent;

use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Trace\LinkInterface;

/**
 * A condition of a {@see ComposableRuleBased} rule.
 *
 * @experimental
 */
interface PredicateInterface
{
    /**
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
