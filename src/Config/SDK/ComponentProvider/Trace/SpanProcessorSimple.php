<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Trace;

use Nevay\OTelSDK\Configuration\ComponentPlugin;
use Nevay\OTelSDK\Configuration\ComponentProvider;
use Nevay\OTelSDK\Configuration\ComponentProviderRegistry;
use Nevay\OTelSDK\Configuration\Context;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * @implements ComponentProvider<SpanProcessorInterface>
 */
final class SpanProcessorSimple implements ComponentProvider
{

    /**
     * @param array{
     *     exporter: ComponentPlugin<SpanExporterInterface>,
     * } $properties
     */
    public function createPlugin(array $properties, Context $context): SpanProcessorInterface
    {
        return new SimpleSpanProcessor(
            exporter: $properties['exporter']->create($context),
        );
    }

    public function getConfig(ComponentProviderRegistry $registry): ArrayNodeDefinition
    {
        $node = new ArrayNodeDefinition('simple');
        $node
            ->children()
                ->append($registry->component('exporter', SpanExporterInterface::class)->isRequired())
            ->end()
        ;

        return $node;
    }
}
