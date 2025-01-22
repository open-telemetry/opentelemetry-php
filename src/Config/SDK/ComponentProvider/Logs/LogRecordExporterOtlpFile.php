<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Logs;

use Nevay\SPI\ServiceProviderDependency\PackageDependency;
use OpenTelemetry\Config\SDK\ComponentProvider\OutputStreamParser;
use OpenTelemetry\Config\SDK\Configuration\ComponentProvider;
use OpenTelemetry\Config\SDK\Configuration\ComponentProviderRegistry;
use OpenTelemetry\Config\SDK\Configuration\Context;
use OpenTelemetry\Config\SDK\Configuration\Validation;
use OpenTelemetry\Contrib\Otlp\ContentTypes;
use OpenTelemetry\Contrib\Otlp\LogsExporter;
use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;
use OpenTelemetry\SDK\Registry;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * @implements ComponentProvider<LogRecordExporterInterface>
 */
#[PackageDependency('open-telemetry/exporter-otlp', '^1.0.5')]
final class LogRecordExporterOtlpFile implements ComponentProvider
{
    /**
     * @param array{
     *     output_stream: string,
     * } $properties
     */
    public function createPlugin(array $properties, Context $context): LogRecordExporterInterface
    {
        $endpoint = OutputStreamParser::parse($properties['output_stream']);

        return new LogsExporter(Registry::transportFactory('stream')->create(
            endpoint: $endpoint,
            contentType: ContentTypes::NDJSON,
        ));
    }

    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('otlp_file');
        $node
            ->children()
                ->scalarNode('output_stream')->defaultValue('stdout')->validate()->always(Validation::ensureString())->end()->end()
            ->end()
        ;

        return $node;
    }
}