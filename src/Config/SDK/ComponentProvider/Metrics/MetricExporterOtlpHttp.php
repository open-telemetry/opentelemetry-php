<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Metrics;

use Nevay\SPI\ServiceProviderDependency\PackageDependency;
use OpenTelemetry\API\Common\Time\ClockInterface;
use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\Config\SDK\Configuration\Validation;
use OpenTelemetry\Contrib\Otlp\ContentTypes;
use OpenTelemetry\Contrib\Otlp\MetricExporter;
use OpenTelemetry\SDK\Common\Configuration\Parser\MapParser;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;
use OpenTelemetry\SDK\Registry;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * @implements ComponentProvider<MetricExporterInterface>
 */
#[PackageDependency('open-telemetry/exporter-otlp', '^1.0.5')]
final class MetricExporterOtlpHttp implements ComponentProvider
{
    /**
     * @param array{
     *     encoding: 'protobuf'|'json',
     *     endpoint: string,
     *     certificate_file: ?string,
     *     client_key_file: ?string,
     *     client_certificate_file: ?string,
     *     headers: list<array{name: string, value: string}>,
     *     headers_list: ?string,
     *     compression: 'gzip'|null,
     *     timeout: int<0, max>,
     *     temporality_preference: 'cumulative'|'delta'|'lowmemory',
     *     default_histogram_aggregation: 'explicit_bucket_histogram|base2_exponential_bucket_histogram',
     * } $properties
     */
    public function createPlugin(array $properties, Context $context): MetricExporterInterface
    {
        $headers = array_column($properties['headers'], 'value', 'name') + MapParser::parse($properties['headers_list']);

        $temporality = match ($properties['temporality_preference']) {
            'cumulative' => Temporality::CUMULATIVE,
            'delta' => Temporality::DELTA,
            'lowmemory' => null,
        };

        return new MetricExporter(Registry::transportFactory('http')->create(
            endpoint: $properties['endpoint'],
            contentType: match ($properties['encoding']) {
                'protobuf' => ContentTypes::PROTOBUF,
                'json' => ContentTypes::JSON,
            },
            headers: $headers,
            compression: $properties['compression'],
            timeout: $properties['timeout'] / ClockInterface::MILLIS_PER_SECOND,
            cacert: $properties['certificate_file'],
            cert: $properties['client_certificate_file'],
            key: $properties['client_certificate_file'],
        ), $temporality);
    }

    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('otlp_http');
        $node
            ->children()
            ->enumNode('encoding')->defaultValue('protobuf')->values(['protobuf', 'json'])->end()
            ->scalarNode('endpoint')->defaultValue('http://localhost:4318/v1/metrics')->validate()->always(Validation::ensureString())->end()->end()
            ->scalarNode('certificate_file')->defaultNull()->validate()->always(Validation::ensureString())->end()->end()
            ->scalarNode('client_key_file')->defaultNull()->validate()->always(Validation::ensureString())->end()->end()
            ->scalarNode('client_certificate_file')->defaultNull()->validate()->always(Validation::ensureString())->end()->end()
            ->arrayNode('headers')
                ->arrayPrototype()
                    ->children()
                        ->scalarNode('name')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('value')->defaultNull()->validate()->always(Validation::ensureString())->end()->end()
                    ->end()
                ->end()
            ->end()
            ->scalarNode('headers_list')->defaultNull()->validate()->always(Validation::ensureString())->end()->end()
            ->enumNode('compression')->values(['gzip'])->defaultNull()->validate()->always(Validation::ensureString())->end()->end()
            ->integerNode('timeout')->min(0)->defaultValue(10000)->end()
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
