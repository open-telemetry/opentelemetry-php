<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider;

use OpenTelemetry\Config\SDK\Configuration\Validation;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * The `OtlpHttpExporter` and `OtlpGrpcExporter` nodes, shared by the span, metric and log record
 * exporters.
 *
 * @internal
 */
final class OtlpExporterNodes
{
    public static function http(NodeBuilder $builder, string $endpoint): ArrayNodeDefinition
    {
        return self::exporter($builder, 'otlp_http', $endpoint, grpc: false);
    }

    public static function grpc(NodeBuilder $builder): ArrayNodeDefinition
    {
        return self::exporter($builder, 'otlp_grpc', 'http://localhost:4317', grpc: true);
    }

    private static function exporter(NodeBuilder $builder, string $name, string $endpoint, bool $grpc): ArrayNodeDefinition
    {
        $node = $builder->arrayNode($name);
        if (!$grpc) {
            $node->children()->enumNode('encoding')->defaultValue('protobuf')->values(['protobuf', 'json'])->end();
        }
        $node
            ->children()
                ->scalarNode('endpoint')->defaultValue($endpoint)->validate()->always(Validation::ensureString())->end()->end()
                ->append(self::tls($builder, insecure: $grpc))
                ->arrayNode('headers')
                    ->beforeNormalization()->ifNull()->then(static fn (): array => [])->end()
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('name')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('value')->defaultNull()->validate()->always(Validation::ensureString())->end()->end()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('headers_list')->defaultNull()->validate()->always(Validation::ensureString())->end()->end()
                ->enumNode('compression')->values(['gzip', 'none', null])->defaultNull()->end()
                ->integerNode('timeout')->min(0)->defaultValue(10000)->end()
            ->end()
        ;

        return $node;
    }

    private static function tls(NodeBuilder $builder, bool $insecure): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('tls');
        $children = $node
            ->addDefaultsIfNotSet()
            ->beforeNormalization()->ifNull()->then(static fn (): array => [])->end()
            ->children()
                ->scalarNode('ca_file')->defaultNull()->validate()->always(Validation::ensureString())->end()->end()
                ->scalarNode('cert_file')->defaultNull()->validate()->always(Validation::ensureString())->end()->end()
                ->scalarNode('key_file')->defaultNull()->validate()->always(Validation::ensureString())->end()->end()
        ;
        if ($insecure) {
            $children->booleanNode('insecure')->defaultNull()->end();
        }

        return $node;
    }
}
