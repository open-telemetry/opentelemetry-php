<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Logs;

use Nevay\OTelSDK\Configuration\ComponentPlugin;
use Nevay\OTelSDK\Configuration\ComponentProvider;
use Nevay\OTelSDK\Configuration\ComponentProviderRegistry;
use Nevay\OTelSDK\Configuration\Context;
use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;
use OpenTelemetry\SDK\Logs\Processor\SimpleLogRecordProcessor;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

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
