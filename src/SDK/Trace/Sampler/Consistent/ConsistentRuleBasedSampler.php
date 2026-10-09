<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Sampler\Consistent;

use function implode;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use function sprintf;

/**
 * Consistent sampler that evaluates a sequence of (predicate → delegate) rules.
 *
 * The first matching rule's delegate provides the sampling intent.
 * If no rule matches (or the spanKind does not match $spanKindToMatch), the span is dropped
 * ({@see ConsistentSamplingUtil::INVALID_THRESHOLD}).
 *
 * @experimental
 */
final class ConsistentRuleBasedSampler extends ConsistentSampler
{
    /** @var list<PredicatedSampler> */
    private readonly array $samplers;
    private readonly string $description;

    /**
     * @param int|null $spanKindToMatch SpanKind to restrict matching to; null = all kinds
     * @param PredicatedSampler ...$samplers (predicate, delegate) pairs, evaluated in order
     */
    public function __construct(
        private readonly ?int $spanKindToMatch,
        PredicatedSampler ...$samplers,
    ) {
        $this->samplers = $samplers;
        $this->description = sprintf(
            'ConsistentRuleBasedSampler{%s}',
            implode(',', array_map(
                static fn (PredicatedSampler $ps) => sprintf(
                    '%s->%s',
                    $ps->getPredicate() instanceof \Stringable ? (string) $ps->getPredicate() : $ps->getPredicate()::class,
                    $ps->getSampler()->getDescription(),
                ),
                $samplers,
            )),
        );
    }

    #[\Override]
    public function getSamplingIntent(
        ContextInterface $parentContext,
        string $spanName,
        int $spanKind,
        AttributesInterface $attributes,
        array $links,
    ): SamplingIntentInterface {
        if ($this->spanKindToMatch !== null && $this->spanKindToMatch !== $spanKind) {
            return new SamplingIntent(threshold: ConsistentSamplingUtil::INVALID_THRESHOLD, adjustedCountReliable: false);
        }

        foreach ($this->samplers as $predicatedSampler) {
            if ($predicatedSampler->getPredicate()->spanMatches($parentContext, $spanName, $spanKind, $attributes, $links)) {
                return $predicatedSampler->getSampler()->getSamplingIntent($parentContext, $spanName, $spanKind, $attributes, $links);
            }
        }

        return new SamplingIntent(threshold: ConsistentSamplingUtil::INVALID_THRESHOLD, adjustedCountReliable: false);
    }

    #[\Override]
    public function getDescription(): string
    {
        return $this->description;
    }
}
