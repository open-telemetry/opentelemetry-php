<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Sampler\Consistent;

/**
 * Associates a {@see PredicateInterface} with a {@see ComposableInterface} for use in
 * {@see ConsistentRuleBasedSampler}.
 *
 * @experimental
 */
final class PredicatedSampler
{
    private function __construct(
        private readonly PredicateInterface $predicate,
        private readonly ComposableInterface $sampler,
    ) {
    }

    public static function onMatch(PredicateInterface $predicate, ComposableInterface $sampler): self
    {
        return new self($predicate, $sampler);
    }

    public function getPredicate(): PredicateInterface
    {
        return $this->predicate;
    }

    public function getSampler(): ComposableInterface
    {
        return $this->sampler;
    }
}
