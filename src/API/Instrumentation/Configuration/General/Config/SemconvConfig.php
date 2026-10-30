<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Instrumentation\Configuration\General\Config;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * `ExperimentalSemconvConfig`: the per-domain semantic convention version and migration settings.
 *
 * Validated so that portable configuration parses, but not yet exposed. Nothing in this SDK
 * consumes it.
 *
 * @internal
 */
final class SemconvConfig
{
    public static function node(NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('semconv');
        $node
            ->ignoreExtraKeys()
            ->children()
                ->integerNode('version')->min(0)->defaultNull()->end()
                ->booleanNode('experimental')->defaultNull()->end()
                ->booleanNode('dual_emit')->defaultNull()->end()
            ->end()
        ;

        return $node;
    }
}
