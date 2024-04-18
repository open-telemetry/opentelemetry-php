<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Trace;

use Nevay\SPI\ServiceProviderDependency\PackageDependency;
use OpenTelemetry\API\Signals;
use OpenTelemetry\Config\SDK\Configuration\ComponentProvider;
use OpenTelemetry\Config\SDK\Configuration\ComponentProviderRegistry;
use OpenTelemetry\Config\SDK\Configuration\Context;
use OpenTelemetry\Config\SDK\Configuration\Validation;
use OpenTelemetry\Contrib\Otlp\OtlpUtil;
use OpenTelemetry\Contrib\Otlp\Protocols;
use OpenTelemetry\Contrib\Otlp\SpanExporter;
use OpenTelemetry\SDK\Registry;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * @implements ComponentProvider<SpanExporterInterface>
 */
#[PackageDependency('open-telemetry/exporter-otlp', '^1.0.5')]
final class SpanExporterOtlp implements ComponentProvider
{

    /**
     * @param array{
     *     protocol: 'http/protobuf'|'http/json'|'grpc',
     *     endpoint: string,
     *     certificate: ?string,
     *     client_key: ?string,
     *     client_certificate: ?string,
     *     headers: array<string, string>,
     *     compression: 'gzip'|null,
     *     timeout: int<0, max>,
     * } $properties
     */
    public function createPlugin(array $properties, Context $context): SpanExporterInterface
    {
        $protocol = $properties['protocol'];

        return new SpanExporter(Registry::transportFactory($protocol)->create(
            endpoint: $properties['endpoint'] . OtlpUtil::path(Signals::TRACE, $protocol),
            contentType: Protocols::contentType($protocol),
            headers: $properties['headers'],
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
                    ->scalarPrototype()->end()
                ->end()
                ->enumNode('compression')->values(['gzip'])->defaultNull()->end()
                ->integerNode('timeout')->min(0)->defaultValue(10)->end()
            ->end()
        ;

        return $node;
    }
}
