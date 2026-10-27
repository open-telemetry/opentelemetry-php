<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Trace;

use InvalidArgumentException;
use OpenTelemetry\API\Configuration\Config\ComponentPlugin;
use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\Config\SDK\Configuration\Validation;
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
     *         span_kinds: list<?string>,
     *         parent: list<?string>,
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

        $rule = $node->children()->arrayNode('rules')->defaultValue([])->requiresAtLeastOneElement()->arrayPrototype();

        $ruleChildren = $rule->children();
        $ruleChildren->append($registry->component('sampler', ComposableSamplerInterface::class)->isRequired());
        $ruleChildren->arrayNode('span_kinds')
            ->defaultValue([])
            ->requiresAtLeastOneElement()
            ->enumPrototype()->values([...array_keys(self::SPAN_KINDS), null])->end()
        ->end();
        $ruleChildren->arrayNode('parent')
            ->defaultValue([])
            ->requiresAtLeastOneElement()
            ->enumPrototype()->values([...self::PARENTS, null])->end()
        ->end();
        $ruleChildren->arrayNode('attribute_values')
            ->children()
                ->scalarNode('key')->isRequired()->cannotBeEmpty()->validate()->always(Validation::ensureString())->end()->end()
                ->arrayNode('values')->isRequired()->cannotBeEmpty()->scalarPrototype()->end()->end()
            ->end()
        ->end();

        $ruleChildren->arrayNode('attribute_patterns')
            ->children()
                ->scalarNode('key')->isRequired()->cannotBeEmpty()->validate()->always(Validation::ensureString())->end()->end()
                ->arrayNode('included')->defaultValue([])->requiresAtLeastOneElement()->scalarPrototype()->end()->end()
                ->arrayNode('excluded')->defaultValue([])->requiresAtLeastOneElement()->scalarPrototype()->end()->end()
            ->end()
        ;

        return $node;
    }

    /**
     * All configured conditions of a rule must match; a rule without conditions matches every span.
     * A null list item matches nothing, so a list of only nulls is no condition at all.
     */
    private static function createPredicate(array $rule): PredicateInterface
    {
        $predicates = [];

        $spanKinds = [];
        foreach ($rule['span_kinds'] ?? [] as $kind) {
            if ($kind !== null) {
                $spanKinds[] = self::SPAN_KINDS[$kind];
            }
        }
        if ($spanKinds !== []) {
            $predicates[] = Predicate::spanKindIn(...$spanKinds);
        }
        // any of the listed parent types matches
        $parentPredicate = null;
        foreach ($rule['parent'] ?? [] as $parent) {
            if ($parent === null) {
                continue;
            }
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
