<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Trace;

use InvalidArgumentException;
use OpenTelemetry\API\Configuration\Config\ComponentPlugin;
use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\Config\SDK\Configuration\Internal\NonEmptyList;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ComposableRuleBased;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ComposableSamplerInterface;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\Predicate;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\PredicatedSampler;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\PredicateInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * @internal
 * @experimental
 * @implements ComponentProvider<ComposableSamplerInterface>
 */
final class ComposableSamplerRuleBased implements ComponentProvider
{
    private const SPAN_KINDS = [
        'internal' => SpanKind::KIND_INTERNAL,
        'server' => SpanKind::KIND_SERVER,
        'client' => SpanKind::KIND_CLIENT,
        'producer' => SpanKind::KIND_PRODUCER,
        'consumer' => SpanKind::KIND_CONSUMER,
    ];
    private const PARENTS = ['none', 'remote', 'local'];

    /**
     * @param array{
     *     rules: list<array{
     *         sampler: ComponentPlugin<ComposableSamplerInterface>,
     *         span_kinds: list<string>,
     *         parent: list<string>,
     *         attribute_values: ?array{key: string, values: list<string>},
     *         attribute_patterns: ?array{key: string, included: list<string>, excluded: list<string>},
     *     }>,
     * } $properties
     */
    #[\Override]
    public function createPlugin(array $properties, Context $context): ComposableSamplerInterface
    {
        $samplers = [];
        foreach ($properties['rules'] as $rule) {
            $samplers[] = new PredicatedSampler(
                self::createPredicate($rule),
                $rule['sampler']->create($context),
            );
        }

        return new ComposableRuleBased(...$samplers);
    }

    #[\Override]
    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('rule_based');
        $node->beforeNormalization()->always(NonEmptyList::normalizer('rules'))->end();

        $rule = $node->children()->arrayNode('rules')->defaultValue([])->arrayPrototype();
        $rule->beforeNormalization()->always(NonEmptyList::normalizer('span_kinds', 'parent'))->end();

        $ruleChildren = $rule->children();
        $ruleChildren->append($registry->component('sampler', ComposableSamplerInterface::class)->isRequired());
        $ruleChildren->arrayNode('span_kinds')
            ->defaultValue([])
            ->enumPrototype()->values(array_keys(self::SPAN_KINDS))->end()
        ->end();
        $ruleChildren->arrayNode('parent')
            ->defaultValue([])
            ->enumPrototype()->values(self::PARENTS)->end()
        ->end();
        $ruleChildren->arrayNode('attribute_values')
            ->children()
                ->scalarNode('key')->isRequired()->cannotBeEmpty()->end()
                ->arrayNode('values')->isRequired()->cannotBeEmpty()->scalarPrototype()->end()->end()
            ->end()
        ->end();

        $patterns = $ruleChildren->arrayNode('attribute_patterns');
        $patterns->beforeNormalization()->always(NonEmptyList::normalizer('included', 'excluded'))->end();
        $patterns
            ->children()
                ->scalarNode('key')->isRequired()->cannotBeEmpty()->end()
                ->arrayNode('included')->defaultValue([])->scalarPrototype()->end()->end()
                ->arrayNode('excluded')->defaultValue([])->scalarPrototype()->end()->end()
            ->end()
        ;

        return $node;
    }

    /**
     * All configured conditions of a rule must match; a rule without conditions matches every span.
     */
    private static function createPredicate(array $rule): PredicateInterface
    {
        $predicates = [];

        if (($rule['span_kinds'] ?? []) !== []) {
            $predicates[] = Predicate::spanKindIn(...array_map(
                static fn (string $kind): int => self::SPAN_KINDS[$kind],
                $rule['span_kinds'],
            ));
        }
        // any of the listed parent types matches
        $parentPredicate = null;
        foreach ($rule['parent'] ?? [] as $parent) {
            $predicate = match ($parent) {
                'none' => Predicate::isRootSpan(),
                'remote' => Predicate::hasRemoteParent(),
                'local' => Predicate::hasLocalSpanParent(),
                default => throw new InvalidArgumentException(sprintf('Unknown parent type "%s"', $parent)),
            };
            $parentPredicate = $parentPredicate === null ? $predicate : Predicate::or($parentPredicate, $predicate);
        }
        if ($parentPredicate !== null) {
            $predicates[] = $parentPredicate;
        }
        if (isset($rule['attribute_values'])) {
            $predicates[] = Predicate::attributeValues($rule['attribute_values']['key'], array_values(array_map(Predicate::attributeValueToString(...), $rule['attribute_values']['values'])));
        }
        if (isset($rule['attribute_patterns'])) {
            $predicates[] = Predicate::attributePatterns(
                $rule['attribute_patterns']['key'],
                array_values(array_map(Predicate::attributeValueToString(...), $rule['attribute_patterns']['included'] ?? [])),
                array_values(array_map(Predicate::attributeValueToString(...), $rule['attribute_patterns']['excluded'] ?? [])),
            );
        }

        $result = null;
        foreach ($predicates as $predicate) {
            $result = $result === null ? $predicate : Predicate::and($result, $predicate);
        }

        return $result ?? Predicate::anySpan();
    }
}
