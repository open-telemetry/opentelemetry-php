<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Logs;

use Nevay\SPI\ServiceProviderDependency\PackageDependency;
use OpenTelemetry\API\Common\Time\ClockInterface;
use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\Config\SDK\Configuration\Validation;
use OpenTelemetry\Contrib\Otlp\ContentTypes;
use OpenTelemetry\Contrib\Otlp\LogsExporter;
use OpenTelemetry\SDK\Common\Configuration\Parser\MapParser;
use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;
use OpenTelemetry\SDK\Registry;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * @implements ComponentProvider<LogRecordExporterInterface>
 */
#[PackageDependency('open-telemetry/exporter-otlp', '^1.0.5')]
final class LogRecordExporterOtlpHttp implements ComponentProvider
{
    /**
     * @param array{
     *     endpoint: string,
     *     tls: array{
     *         ca_file: ?string,
     *         cert_file: ?string,
     *         key_file: ?string,
     *     },
     *     headers: list<array{name: string, value: string}>,
     *     headers_list: ?string,
     *     compression: 'gzip'|null,
     *     max_request_size: ?int<0, max>,
     *     max_response_size: ?int<1, max>,
     *     timeout: int<0, max>,
     *     encoding: 'protobuf'|'json',
     * } $properties
     */
    #[\Override]
    public function createPlugin(array $properties, Context $context): LogRecordExporterInterface
    {
        $headers = array_column($properties['headers'], 'value', 'name') + MapParser::parse($properties['headers_list']);

        return new LogsExporter(Registry::transportFactory('http')->create(
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
        ));
    }

    #[\Override]
    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('otlp_http');
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

                    return $v;
                })
            ->end()
            ->children()
                ->enumNode('encoding')->defaultValue('protobuf')->values(['protobuf', 'json'])->end()
                ->scalarNode('endpoint')->defaultValue('http://localhost:4318/v1/logs')->validate()->always(Validation::ensureString())->end()->end()
                ->arrayNode('tls')
                    ->addDefaultsIfNotSet()
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
                ->enumNode('compression')->values(['gzip'])->defaultNull()->end()
                ->integerNode('max_request_size')->min(0)->defaultNull()->end() // TODO: wire when transport supports it
                ->integerNode('max_response_size')->min(1)->defaultNull()->end() // TODO: wire when transport supports it
                ->integerNode('timeout')->min(0)->defaultValue(10000)->end()
            ->end()
        ;

        return $node;
    }
}
