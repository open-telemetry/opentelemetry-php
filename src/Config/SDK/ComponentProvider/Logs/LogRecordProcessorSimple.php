<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Logs;

use OpenTelemetry\Config\SDK\Configuration\ComponentPlugin;
use OpenTelemetry\Config\SDK\Configuration\ComponentProvider;
use OpenTelemetry\Config\SDK\Configuration\ComponentProviderRegistry;
use OpenTelemetry\Config\SDK\Configuration\Context;
use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;
use OpenTelemetry\SDK\Logs\Processor\SimpleLogRecordProcessor;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

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
    public function createPlugin(array $properties, Context $context): LogRecordProcessorInterface
    {
        return new SimpleLogRecordProcessor(
            exporter: $properties['exporter']->create($context),
        );
    }

    public function getConfig(ComponentProviderRegistry $registry): ArrayNodeDefinition
    {
        $node = new ArrayNodeDefinition('simple');
        $node
            ->children()
                ->append($registry->component('exporter', LogRecordExporterInterface::class)->isRequired())
            ->end()
        ;

        return $node;
    }
}
