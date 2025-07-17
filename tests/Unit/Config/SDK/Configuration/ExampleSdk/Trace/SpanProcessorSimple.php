<?php

declare(strict_types=1);

namespace ExampleSDK\ComponentProvider\Trace;

use BadMethodCallException;
use ExampleSDK\Trace\SpanExporter;
use ExampleSDK\Trace\SpanProcessor;
use OpenTelemetry\API\Configuration\Config\ComponentPlugin;
use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

final class SpanProcessorSimple implements ComponentProvider
{
    /**
     * @param array{
     *     exporter: ComponentPlugin<SpanExporter>,
     * } $properties
     */
    #[\Override]
    public function createPlugin(array $properties, Context $context): SpanProcessor
    {
        throw new BadMethodCallException('not implemented');
    }

    #[\Override]
    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('simple');
        $node
            ->children()
                ->append($registry->component('exporter', SpanExporter::class)->isRequired())
            ->end()
        ;

        return $node;
    }
}
