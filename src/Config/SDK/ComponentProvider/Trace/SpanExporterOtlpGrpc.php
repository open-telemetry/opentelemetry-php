<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Trace;

use Nevay\SPI\ServiceProviderDependency\PackageDependency;
use OpenTelemetry\API\Common\Time\ClockInterface;
use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\API\Signals;
use OpenTelemetry\Config\SDK\Configuration\Validation;
use OpenTelemetry\Contrib\Otlp\OtlpUtil;
use OpenTelemetry\Contrib\Otlp\Protocols;
use OpenTelemetry\Contrib\Otlp\SpanExporter;
use OpenTelemetry\SDK\Common\Configuration\Parser\MapParser;
use OpenTelemetry\SDK\Registry;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * @implements ComponentProvider<SpanExporterInterface>
 */
#[PackageDependency('open-telemetry/exporter-otlp', '^1.0.5')]
#[PackageDependency('open-telemetry/transport-grpc', '^1')]
final class SpanExporterOtlpGrpc implements ComponentProvider
{
    /**
     * @param array{
     *     endpoint: string,
     *     certificate_file: ?string,
     *     client_key_file: ?string,
     *     client_certificate_file: ?string,
     *     headers: list<array{name: string, value: string}>,
     *     headers_list: ?string,
     *     compression: 'gzip'|null,
     *     timeout: int<0, max>,
     *     insecure: ?bool,
     * } $properties
     */
    #[\Override]
    public function createPlugin(array $properties, Context $context): SpanExporterInterface
    {
        $protocol = Protocols::GRPC;
        $headers = array_column($properties['headers'], 'value', 'name') + MapParser::parse($properties['headers_list']);

        return new SpanExporter(Registry::transportFactory($protocol)->create(
            endpoint: $properties['endpoint'] . OtlpUtil::path(Signals::TRACE, $protocol),
            contentType: Protocols::contentType($protocol),
            headers: $headers,
            compression: $properties['compression'],
            timeout: $properties['timeout'] / ClockInterface::MILLIS_PER_SECOND,
            cacert: $properties['certificate_file'],
            cert: $properties['client_certificate_file'],
            key: $properties['client_certificate_file'],
        ));
    }

    #[\Override]
    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('otlp_grpc');
        $node
            ->children()
                ->scalarNode('endpoint')->defaultValue('http://localhost:4317')->validate()->always(Validation::ensureString())->end()->end()
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
                ->enumNode('compression')->values(['gzip'])->defaultNull()->end()
                ->integerNode('timeout')->min(0)->defaultValue(10000)->end()
                ->booleanNode('insecure')->defaultNull()->end()
            ->end()
        ;

        return $node;
    }
}
