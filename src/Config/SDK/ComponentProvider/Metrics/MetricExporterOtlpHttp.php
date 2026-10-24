<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Metrics;

use Nevay\SPI\ServiceProviderDependency\PackageDependency;
use OpenTelemetry\API\Common\Time\ClockInterface;
use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\Config\SDK\ComponentProvider\OtlpExporterNodes;
use OpenTelemetry\Contrib\Otlp\ContentTypes;
use OpenTelemetry\Contrib\Otlp\MetricExporter;
use OpenTelemetry\Contrib\Otlp\OtlpUtil;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;
use OpenTelemetry\SDK\Metrics\TemporalityPreference;
use OpenTelemetry\SDK\Registry;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * @internal
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
     *     default_histogram_aggregation: 'explicit_bucket_histogram'|'base2_exponential_bucket_histogram',
     * } $properties
     */
    #[\Override]
    public function createPlugin(array $properties, Context $context): MetricExporterInterface
    {
        $headers = OtlpUtil::headers($properties['headers'], $properties['headers_list']);

        $temporality = match ($properties['temporality_preference']) {
            'cumulative' => TemporalityPreference::cumulative(),
            'delta' => TemporalityPreference::delta(),
            'low_memory' => TemporalityPreference::lowMemory(),
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
        $node = OtlpExporterNodes::http($builder, 'http://localhost:4318/v1/metrics');
        $node
            ->children()
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
