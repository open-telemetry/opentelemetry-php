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
use OpenTelemetry\Contrib\Otlp\OtlpUtil;
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
     *     tls: array{
     *         ca_file: ?string,
     *         cert_file: ?string,
     *         key_file: ?string,
     *     },
     *     headers: list<array{name: non-empty-string, value: ?string}>,
     *     headers_list: ?string,
     *     compression: 'gzip'|'none'|null,
     *     timeout: int<0, max>,
     *     temporality_preference: 'cumulative'|'delta'|'low_memory',
     *     default_histogram_aggregation: 'explicit_bucket_histogram|base2_exponential_bucket_histogram',
     * } $properties
     */
    #[\Override]
    public function createPlugin(array $properties, Context $context): MetricExporterInterface
    {
        $headers = OtlpUtil::headers($properties['headers'], $properties['headers_list']);

        $temporality = match ($properties['temporality_preference']) {
            'cumulative' => Temporality::CUMULATIVE,
            'delta' => Temporality::DELTA,
            'low_memory' => null,
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
            cacert: $properties['tls']['ca_file'],
            cert: $properties['tls']['cert_file'],
            key: $properties['tls']['key_file'],
        ), $temporality);
    }

    #[\Override]
    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('otlp_http');
        $node
            ->children()
            ->enumNode('encoding')->defaultValue('protobuf')->values(['protobuf', 'json'])->end()
            ->scalarNode('endpoint')->defaultValue('http://localhost:4318/v1/metrics')->validate()->always(Validation::ensureString())->end()->end()
            ->arrayNode('tls')
                ->addDefaultsIfNotSet()
                ->beforeNormalization()->ifNull()->then(static fn (): array => [])->end()
                ->children()
                    ->scalarNode('ca_file')->defaultNull()->validate()->always(Validation::ensureString())->end()->end()
                    ->scalarNode('cert_file')->defaultNull()->validate()->always(Validation::ensureString())->end()->end()
                    ->scalarNode('key_file')->defaultNull()->validate()->always(Validation::ensureString())->end()->end()
                ->end()
            ->end()
            ->arrayNode('headers')
                ->arrayPrototype()
                    ->children()
                        ->scalarNode('name')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('value')->defaultNull()->validate()->always(Validation::ensureString())->end()->end()
                    ->end()
                ->end()
            ->end()
            ->scalarNode('headers_list')->defaultNull()->validate()->always(Validation::ensureString())->end()->end()
            ->enumNode('compression')->values(['gzip', 'none', null])->defaultNull()->validate()->always(Validation::ensureString())->end()->end()
            ->integerNode('timeout')->min(0)->defaultValue(10000)->end()
            ->enumNode('temporality_preference')
                ->values(['cumulative', 'delta', 'low_memory'])
                ->defaultValue('cumulative')
            ->end()
            // TODO honour default_histogram_aggregation
            ->enumNode('default_histogram_aggregation')
                ->values(['explicit_bucket_histogram', 'base2_exponential_bucket_histogram'])
                ->defaultValue('explicit_bucket_histogram')
            ->end()
        ->end()
        ;

        return $node;
    }
}
