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
use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;
use OpenTelemetry\SDK\Registry;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * @implements ComponentProvider<LogRecordExporterInterface>
 */
#[PackageDependency('open-telemetry/exporter-otlp', '^1.0.5')]
#[PackageDependency('open-telemetry/transport-grpc', '^1')]
final class LogRecordExporterOtlpGrpc implements ComponentProvider
{
    /**
     * @param array{
     *     endpoint: string,
     *     certificate: ?string,
     *     client_key: ?string,
     *     client_certificate: ?string,
     *     headers: list<array{name: string, value: string}>,
     *     headers_list: ?string,
     *     compression: 'gzip'|null,
     *     timeout: int<0, max>,
     *     insecure: ?bool,
     * } $properties
     */
    public function createPlugin(array $properties, Context $context): LogRecordExporterInterface
    {
        $protocol = Protocols::GRPC;
        $headers = array_column($properties['headers'], 'value', 'name') + MapParser::parse($properties['headers_list']);

        return new LogsExporter(Registry::transportFactory($protocol)->create(
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

    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('otlp_grpc');
        $node
            ->children()
                ->scalarNode('endpoint')->defaultValue('http://localhost:4317')->validate()->always(Validation::ensureString())->end()->end()
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
                ->booleanNode('insecure')->defaultNull()->end()
            ->end()
        ;

        return $node;
    }
}
