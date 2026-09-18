<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Metrics;

use Nevay\SPI\ServiceProviderDependency\PackageDependency;
use OpenTelemetry\API\Common\Time\ClockInterface;
use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\API\Signals;
use OpenTelemetry\Config\SDK\Configuration\Validation;
use OpenTelemetry\Contrib\Otlp\MetricExporter;
use OpenTelemetry\Contrib\Otlp\OtlpUtil;
use OpenTelemetry\Contrib\Otlp\Protocols;
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
#[PackageDependency('open-telemetry/transport-grpc', '^1')]
final class MetricExporterOtlpGrpc implements ComponentProvider
{
    /**
     * @param array{
     *     endpoint: string,
     *     tls: array{
     *         ca_file: ?string,
     *         cert_file: ?string,
     *         key_file: ?string,
     *         insecure: ?bool,
     *     },
     *     headers: list<array{name: string, value: string}>,
     *     headers_list: ?string,
     *     compression: 'gzip'|null,
     *     max_request_size: ?int<0, max>,
     *     max_response_size: ?int<1, max>,
     *     timeout: int<0, max>,
     *     temporality_preference: 'cumulative'|'delta'|'lowmemory',
     *     default_histogram_aggregation: 'explicit_bucket_histogram|base2_exponential_bucket_histogram',
     * } $properties
     */
    #[\Override]
    public function createPlugin(array $properties, Context $context): MetricExporterInterface
    {
        $protocol = Protocols::GRPC;

        $headers = array_column($properties['headers'], 'value', 'name') + MapParser::parse($properties['headers_list']);

        $temporality = match ($properties['temporality_preference']) {
            'cumulative' => Temporality::CUMULATIVE,
            'delta' => Temporality::DELTA,
            'lowmemory' => null,
        };

        return new MetricExporter(Registry::transportFactory($protocol)->create(
            endpoint: $properties['endpoint'] . OtlpUtil::path(Signals::METRICS, $protocol),
            contentType: Protocols::contentType($protocol),
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
        $node = $builder->arrayNode('otlp_grpc');
        $node
            ->beforeNormalization()
                ->ifArray()
                ->then(static function (array $v): array {
                    // Backward compatibility: migrate 1.0-rc.2 flat TLS fields to 1.1 tls: sub-object
                    if (!isset($v['tls']) && (
                        array_key_exists('certificate_file', $v) ||
                        array_key_exists('client_key_file', $v) ||
                        array_key_exists('client_certificate_file', $v)
                    )) {
                        $v['tls'] = [
                            'ca_file'   => $v['certificate_file'] ?? null,
                            'key_file'  => $v['client_key_file'] ?? null,
                            'cert_file' => $v['client_certificate_file'] ?? null,
                        ];
                        unset($v['certificate_file'], $v['client_key_file'], $v['client_certificate_file']);
                    }
                    if (array_key_exists('insecure', $v) && !isset($v['tls']['insecure'])) {
                        $v['tls']['insecure'] = $v['insecure'];
                        unset($v['insecure']);
                    }

                    return $v;
                })
            ->end()
            ->children()
                ->scalarNode('endpoint')->defaultValue('http://localhost:4317')->validate()->always(Validation::ensureString())->end()->end()
                ->arrayNode('tls')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('ca_file')->defaultNull()->validate()->always(Validation::ensureString())->end()->end()
                        ->scalarNode('cert_file')->defaultNull()->validate()->always(Validation::ensureString())->end()->end()
                        ->scalarNode('key_file')->defaultNull()->validate()->always(Validation::ensureString())->end()->end()
                        ->booleanNode('insecure')->defaultNull()->end()
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
                ->enumNode('compression')->values(['gzip'])->defaultNull()->end()
                ->integerNode('max_request_size')->min(0)->defaultNull()->end() // TODO: wire when transport supports it
                ->integerNode('max_response_size')->min(1)->defaultNull()->end() // TODO: wire when transport supports it
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
