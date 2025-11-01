<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Logs;

use OpenTelemetry\API\Configuration\Config\ComponentPlugin;
use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;
use OpenTelemetry\SDK\Logs\Processor\SimpleLogRecordProcessor;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * @implements ComponentProvider<LogRecordProcessorInterface>
 */
final class LogRecordProcessorSimple implements ComponentProvider
{
    /**
     * @param array{
     *     exporter: ComponentPlugin<LogRecordExporterInterface>,
     * } $properties
     */
    #[\Override]
    public function createPlugin(array $properties, Context $context): LogRecordProcessorInterface
    {
        return new SimpleLogRecordProcessor(
            exporter: $properties['exporter']->create($context),
        );
    }

    #[\Override]
    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('simple');
        $node
            ->children()
                ->append($registry->component('exporter', LogRecordExporterInterface::class)->isRequired())
            ->end()
        ;

        return $node;
    }
}
