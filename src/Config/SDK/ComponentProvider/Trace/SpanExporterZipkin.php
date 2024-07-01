<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Trace;

use Nevay\SPI\ServiceProviderDependency\PackageDependency;
use OpenTelemetry\Config\SDK\Configuration\ComponentProvider;
use OpenTelemetry\Config\SDK\Configuration\ComponentProviderRegistry;
use OpenTelemetry\Config\SDK\Configuration\Context;
use OpenTelemetry\Config\SDK\Configuration\Validation;
use OpenTelemetry\Contrib\Zipkin;
use OpenTelemetry\SDK\Registry;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * @implements ComponentProvider<SpanExporterInterface>
 */
#[PackageDependency('open-telemetry/exporter-zipkin', '^1.0')]
final class SpanExporterZipkin implements ComponentProvider
{

    /**
     * @param array{
     *     endpoint: string,
     *     timeout: int<0, max>,
     *     retry: array{
     *          initial_delay: int,
     *          max_attempts: int
     *      }
     * } $properties
     */
    public function createPlugin(array $properties, Context $context): SpanExporterInterface
    {
        return new Zipkin\Exporter(Registry::transportFactory('http')->create(
            endpoint: $properties['endpoint'],
            contentType: 'application/json',
            timeout: $properties['timeout'],
            retryDelay: $properties['retry']['initial_delay'],
            maxRetries: $properties['retry']['max_attempts'],
        ));
    }

    public function getConfig(ComponentProviderRegistry $registry): ArrayNodeDefinition
    {
        $node = new ArrayNodeDefinition('zipkin');
        $node
            ->children()
                ->scalarNode('endpoint')->isRequired()->validate()->always(Validation::ensureString())->end()->end()
                ->integerNode('timeout')->min(0)->defaultValue(10)->end()
                ->arrayNode('retry')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('max_attempts')->min(0)->defaultValue(3)->end()
                        ->integerNode('initial_delay')->min(0)->defaultValue(0)->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }
}
