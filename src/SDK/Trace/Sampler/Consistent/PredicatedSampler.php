<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Sampler\Consistent;

/**
 * A rule of {@see ComposableRuleBased}: the sampler is used for spans that match the predicate.
 *
 * @experimental
 */
final class PredicatedSampler
{
    public function __construct(
        private readonly PredicateInterface $predicate,
        private readonly ComposableSamplerInterface $sampler,
    ) {
    }

    public function getPredicate(): PredicateInterface
    {
        return $this->predicate;
    }

    public function getSampler(): ComposableSamplerInterface
    {
        return $this->sampler;
    }
}
