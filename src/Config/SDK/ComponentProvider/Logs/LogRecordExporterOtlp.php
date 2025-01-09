<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Logs;

use Nevay\SPI\ServiceProviderDependency\PackageDependency;
use OpenTelemetry\API\Signals;
use OpenTelemetry\Config\SDK\Configuration\ComponentProvider;
use OpenTelemetry\Config\SDK\Configuration\ComponentProviderRegistry;
use OpenTelemetry\Config\SDK\Configuration\Context;
use OpenTelemetry\Config\SDK\Configuration\Validation;
use OpenTelemetry\Contrib\Otlp\LogsExporter;
use OpenTelemetry\Contrib\Otlp\OtlpUtil;
use OpenTelemetry\Contrib\Otlp\Protocols;
use OpenTelemetry\SDK\Common\Configuration\Parser\MapParser;
use OpenTelemetry\SDK\Common\Services\Loader;
use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * @implements ComponentProvider<LogRecordExporterInterface>
 */
#[PackageDependency('open-telemetry/exporter-otlp', '^1.0.5')]
final class LogRecordExporterOtlp implements ComponentProvider
{
    /**
     * @param array{
     *     protocol: 'http/protobuf'|'http/json'|'grpc',
     *     endpoint: string,
     *     certificate: ?string,
     *     client_key: ?string,
     *     client_certificate: ?string,
     *     headers: list<array{name: string, value: string}>,
     *     headers_list: ?string,
     *     compression: 'gzip'|null,
     *     timeout: int<0, max>,
     * } $properties
     */
    public function createPlugin(array $properties, Context $context): LogRecordExporterInterface
    {
        $protocol = $properties['protocol'];

        $headers = array_column($properties['headers'], 'value', 'name') + MapParser::parse($properties['headers_list']);

        return new LogsExporter(Loader::transportFactory($protocol)->create(
            endpoint: $properties['endpoint'] . OtlpUtil::path(Signals::LOGS, $protocol),
            contentType: Protocols::contentType($protocol),
            headers: $headers,
            compression: $properties['compression'],
            timeout: $properties['timeout'],
            cacert: $properties['certificate'],
            cert: $properties['client_certificate'],
            key: $properties['client_certificate'],
        ));
    }

    public function getConfig(ComponentProviderRegistry $registry): ArrayNodeDefinition
    {
        $node = new ArrayNodeDefinition('otlp');
        $node
            ->children()
                ->enumNode('protocol')->isRequired()->values(['http/protobuf', 'http/json', 'grpc'])->end()
                ->scalarNode('endpoint')->isRequired()->validate()->always(Validation::ensureString())->end()->end()
                ->scalarNode('certificate')->defaultNull()->validate()->always(Validation::ensureString())->end()->end()
                ->scalarNode('client_key')->defaultNull()->validate()->always(Validation::ensureString())->end()->end()
                ->scalarNode('client_certificate')->defaultNull()->validate()->always(Validation::ensureString())->end()->end()
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
                ->integerNode('timeout')->min(0)->defaultValue(10)->end()
            ->end()
        ;

        return $node;
    }
}
