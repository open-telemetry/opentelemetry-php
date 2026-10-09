<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Integration\Config\ComponentProvider\Trace;

use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\Config\SDK\Configuration\Validation;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * Not to be confused with the contrib `php_rule_based` sampler, which is a different shape:
 * it is a flat list of rule sets each with one `delegate` plus an overall `fallback`, and its
 * rules match on a single regex `pattern`. This node is the schema's `rule_based`, whose rules
 * nest a composable sampler recursively, distinguish exact `values` from wildcard
 * `included`/`excluded`, and fall through to "not sampled" when nothing matches.
 *
 * @implements ComponentProvider<ComposableSamplerInterface>
 */
final class ComposableSamplerRuleBased implements ComponentProvider
{
    private const SPAN_KINDS = ['internal', 'server', 'client', 'producer', 'consumer'];
    private const SPAN_PARENTS = ['none', 'remote', 'local'];

    #[\Override]
    public function createPlugin(array $properties, Context $context): ComposableSamplerInterface
    {
        return new NoopComposableSampler();
    }

    #[\Override]
    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('rule_based');
        $node
            ->children()
                ->arrayNode('rules')
                    ->requiresAtLeastOneElement()
                    ->arrayPrototype()
                        ->children()
                            ->arrayNode('attribute_values')
                                ->children()
                                    ->scalarNode('key')->isRequired()->cannotBeEmpty()
                                        ->validate()->always(Validation::ensureString())->end()
                                    ->end()
                                    ->arrayNode('values')
                                        ->isRequired()
                                        ->requiresAtLeastOneElement()
                                        ->scalarPrototype()->validate()->always(Validation::ensureString())->end()->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('attribute_patterns')
                                ->children()
                                    ->scalarNode('key')->isRequired()->cannotBeEmpty()
                                        ->validate()->always(Validation::ensureString())->end()
                                    ->end()
                                    ->arrayNode('included')
                                        ->requiresAtLeastOneElement()
                                        ->scalarPrototype()->validate()->always(Validation::ensureString())->end()->end()
                                    ->end()
                                    ->arrayNode('excluded')
                                        ->requiresAtLeastOneElement()
                                        ->scalarPrototype()->validate()->always(Validation::ensureString())->end()->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('span_kinds')
                                ->requiresAtLeastOneElement()
                                ->enumPrototype()->values(self::SPAN_KINDS)->end()
                            ->end()
                            ->arrayNode('parent')
                                ->requiresAtLeastOneElement()
                                ->enumPrototype()->values(self::SPAN_PARENTS)->end()
                            ->end()
                            ->append($registry->component('sampler', ComposableSamplerInterface::class)->isRequired())
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }
}
