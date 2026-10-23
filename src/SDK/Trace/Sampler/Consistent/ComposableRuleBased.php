<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Sampler\Consistent;

use function implode;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use function sprintf;

/**
 * Evaluates (predicate, sampler) rules in order and returns the intent of the first matching rule.
 * When no rule matches the span is dropped.
 *
 * @experimental
 */
final class ComposableRuleBased implements ComposableSamplerInterface
{
    /** @var list<PredicatedSampler> */
    private readonly array $rules;
    private readonly string $description;

    public function __construct(PredicatedSampler ...$rules)
    {
        $this->rules = array_values($rules);
        $this->description = sprintf(
            'ComposableRuleBased{%s}',
            implode(',', array_map(
                static fn (PredicatedSampler $rule): string => sprintf(
                    '%s:%s',
                    $rule->getPredicate() instanceof \Stringable ? (string) $rule->getPredicate() : $rule->getPredicate()::class,
                    $rule->getSampler()->getDescription(),
                ),
                $rules,
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
        foreach ($this->rules as $rule) {
            if ($rule->getPredicate()->spanMatches($parentContext, $spanName, $spanKind, $attributes, $links)) {
                return $rule->getSampler()->getSamplingIntent($parentContext, $spanName, $spanKind, $attributes, $links);
            }
        }

        return new SamplingIntent(threshold: SamplingIntent::INVALID_THRESHOLD, adjustedCountReliable: false);
    }

    #[\Override]
    public function getDescription(): string
    {
        return $this->description;
    }
}
