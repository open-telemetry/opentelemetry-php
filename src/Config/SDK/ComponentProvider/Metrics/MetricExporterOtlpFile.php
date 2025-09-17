<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Metrics;

use Nevay\SPI\ServiceProviderDependency\PackageDependency;
use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\Config\SDK\ComponentProvider\OutputStreamParser;
use OpenTelemetry\Config\SDK\Configuration\Validation;
use OpenTelemetry\Contrib\Otlp\ContentTypes;
use OpenTelemetry\Contrib\Otlp\MetricExporter;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;
use OpenTelemetry\SDK\Registry;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * @implements ComponentProvider<MetricExporterInterface>
 */
#[PackageDependency('open-telemetry/exporter-otlp', '^1.0.5')]
final class MetricExporterOtlpFile implements ComponentProvider
{
    /**
     * @param array{
     *     output_stream: string,
     *     temporality_preference: 'cumulative'|'delta'|'lowmemory',
     *      default_histogram_aggregation: 'explicit_bucket_histogram|base2_exponential_bucket_histogram',
     * } $properties
     */
    #[\Override]
    public function createPlugin(array $properties, Context $context): MetricExporterInterface
    {
        $endpoint = OutputStreamParser::parse($properties['output_stream']);

        $temporality = match ($properties['temporality_preference']) {
            'cumulative' => Temporality::CUMULATIVE,
            'delta' => Temporality::DELTA,
            'lowmemory' => null,
        };

        return new MetricExporter(Registry::transportFactory('stream')->create(
            endpoint: $endpoint,
            contentType: ContentTypes::NDJSON,
        ), $temporality);
    }

    #[\Override]
    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('otlp_file/development');
        $node
            ->children()
                ->scalarNode('output_stream')->defaultValue('stdout')->validate()->always(Validation::ensureString())->end()->end()
                ->enumNode('temporality_preference')
                    ->values(['cumulative', 'delta', 'lowmemory'])
                    ->defaultValue('cumulative')
                ->end()
                ->enumNode('default_histogram_aggregation')
                    ->values(['explicit_bucket_histogram', 'base2_exponential_bucket_histogram'])
                    ->defaultValue('explicit_bucket_histogram')
                ->end()
            ->end()
        ;

        return $node;
    }
}
