<?php

declare(strict_types=1);

namespace ExampleSDK\ComponentProvider\Logs;

use BadMethodCallException;
use ExampleSDK\Logs\LogRecordExporter;
use ExampleSDK\Logs\LogRecordProcessor;
use OpenTelemetry\API\Configuration\Config\ComponentPlugin;
use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

final class LogRecordProcessorSimple implements ComponentProvider
{
    /**
     * @param array{
     *     exporter: ComponentPlugin<LogRecordExporter>,
     * } $properties
     */
    #[\Override]
    public function createPlugin(array $properties, Context $context): LogRecordProcessor
    {
        throw new BadMethodCallException('not implemented');
    }

    #[\Override]
    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('simple');
        $node
            ->children()
                ->append($registry->component('exporter', LogRecordExporter::class)->isRequired())
            ->end()
        ;

        return $node;
    }
}
