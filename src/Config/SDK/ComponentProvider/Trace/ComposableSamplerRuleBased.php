<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Trace;

use InvalidArgumentException;
use OpenTelemetry\API\Configuration\Config\ComponentPlugin;
use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ComposableInterface;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ConsistentSampler;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\Predicate;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\PredicatedSampler;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\PredicateInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * @experimental
 * @implements ComponentProvider<ComposableInterface>
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
     *         sampler: ComponentPlugin<ComposableInterface>,
     *         span_kinds: list<string>,
     *         parent: list<string>,
     *         attribute_values: ?array{key: string, values: list<string>},
     *         attribute_patterns: ?array{key: string, included: list<string>, excluded: list<string>},
     *     }>,
     * } $properties
     */
    #[\Override]
    public function createPlugin(array $properties, Context $context): ComposableInterface
    {
        $samplers = [];
        foreach ($properties['rules'] as $rule) {
            $samplers[] = PredicatedSampler::onMatch(
                self::createPredicate($rule),
                $rule['sampler']->create($context),
            );
        }

        return ConsistentSampler::ruleBased(null, ...$samplers);
    }

    #[\Override]
    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('rule_based');
        $node
            ->children()
                ->arrayNode('rules')
                    ->defaultValue([])
                    ->arrayPrototype()
                        ->children()
                            ->append($registry->component('sampler', ComposableInterface::class)->isRequired())
                            ->arrayNode('span_kinds')
                                ->defaultValue([])
                                ->enumPrototype()->values(array_keys(self::SPAN_KINDS))->end()
                            ->end()
                            ->arrayNode('parent')
                                ->defaultValue([])
                                ->enumPrototype()->values(self::PARENTS)->end()
                            ->end()
                            ->arrayNode('attribute_values')
                                ->children()
                                    ->scalarNode('key')->isRequired()->cannotBeEmpty()->end()
                                    ->arrayNode('values')->isRequired()->cannotBeEmpty()->scalarPrototype()->end()->end()
                                ->end()
                            ->end()
                            ->arrayNode('attribute_patterns')
                                ->children()
                                    ->scalarNode('key')->isRequired()->cannotBeEmpty()->end()
                                    ->arrayNode('included')->defaultValue([])->scalarPrototype()->end()->end()
                                    ->arrayNode('excluded')->defaultValue([])->scalarPrototype()->end()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
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
        if (($rule['parent'] ?? []) !== []) {
            $predicates[] = array_reduce(
                array_map(
                    static fn (string $parent): Predicate => match ($parent) {
                        'none' => Predicate::isRootSpan(),
                        'remote' => Predicate::hasRemoteParent(),
                        'local' => Predicate::hasLocalSpanParent(),
                        default => throw new InvalidArgumentException(sprintf('Unknown parent type "%s"', $parent)),
                    },
                    $rule['parent'],
                ),
                static fn (?Predicate $carry, Predicate $next): Predicate => $carry === null ? $next : Predicate::or($carry, $next),
            );
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

        return array_reduce(
            $predicates,
            static fn (?Predicate $carry, Predicate $next): Predicate => $carry === null ? $next : Predicate::and($carry, $next),
        ) ?? Predicate::anySpan();
    }
}
