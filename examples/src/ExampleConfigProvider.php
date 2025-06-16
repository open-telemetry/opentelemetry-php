<?php

declare(strict_types=1);

namespace OpenTelemetry\Example;

use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\InstrumentationConfiguration;
use OpenTelemetry\Config\SDK\Configuration\Validation;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * @implements ComponentProvider<InstrumentationConfiguration>
 */
final class ExampleConfigProvider implements ComponentProvider
{
    /**
     * @psalm-suppress MoreSpecificImplementedParamType
     * @param array{
     *     span_name: string,
     *     enabled: bool,
     * } $properties
     */
    public function createPlugin(array $properties, Context $context): InstrumentationConfiguration
    {
        return new ExampleConfig(
            spanName: $properties['span_name'],
            enabled: $properties['enabled'],
        );
    }

    /**
     * @psalm-suppress UndefinedInterfaceMethod,PossiblyNullReference
     */
    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('example_instrumentation');
        $node
            ->children()
                ->scalarNode('span_name')->isRequired()->validate()->always(Validation::ensureString())->end()->end()
            ->end()
            ->canBeDisabled()
        ;

        return $node;
    }
}
