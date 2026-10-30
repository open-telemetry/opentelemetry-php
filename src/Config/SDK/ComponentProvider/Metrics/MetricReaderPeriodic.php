<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Metrics;

use function array_is_list;
use function array_key_first;
use function count;
use InvalidArgumentException;
use function is_array;
use function is_string;
use OpenTelemetry\API\Configuration\Config\ComponentPlugin;
use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;
use OpenTelemetry\SDK\Metrics\MetricReader\ExportingReader;
use OpenTelemetry\SDK\Metrics\MetricReaderInterface;
use function sprintf;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * @internal
 * @implements ComponentProvider<MetricReaderInterface>
 */
final class MetricReaderPeriodic implements ComponentProvider
{
    /**
     * @param array{
     *     interval: int<0, max>,
     *     timeout: int<0, max>,
     *     exporter: ComponentPlugin<MetricExporterInterface>,
     *     producers: array,
     *     cardinality_limits: array{
     *         default: ?int<1, max>,
     *         counter: ?int<1, max>,
     *         gauge: ?int<1, max>,
     *         histogram: ?int<1, max>,
     *         observable_counter: ?int<1, max>,
     *         observable_gauge: ?int<1, max>,
     *         observable_up_down_counter: ?int<1, max>,
     *         up_down_counter: ?int<1, max>,
     *     },
     * } $properties
     */
    #[\Override]
    public function createPlugin(array $properties, Context $context): MetricReaderInterface
    {
        return new ExportingReader(
            exporter: $properties['exporter']->create($context),
        );
    }

    #[\Override]
    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('periodic');
        $node
            ->children()
                ->integerNode('interval')->min(0)->defaultValue(60000)->end()
                ->integerNode('timeout')->min(0)->defaultValue(30000)->end()
                ->append($registry->component('exporter', MetricExporterInterface::class)->isRequired())
                ->arrayNode('producers') //TODO not implemented in metrics SDK
                    ->requiresAtLeastOneElement()
                    ->variablePrototype()->validate()->always(self::validateProducer(...))->end()->end()
                ->end()
                ->arrayNode('cardinality_limits') //TODO not implemented in metrics SDK
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('default')->defaultNull()->min(1)->end()
                        ->integerNode('counter')->defaultNull()->min(1)->end()
                        ->integerNode('gauge')->defaultNull()->min(1)->end()
                        ->integerNode('histogram')->defaultNull()->min(1)->end()
                        ->integerNode('observable_counter')->defaultNull()->min(1)->end()
                        ->integerNode('observable_gauge')->defaultNull()->min(1)->end()
                        ->integerNode('observable_up_down_counter')->defaultNull()->min(1)->end()
                        ->integerNode('up_down_counter')->defaultNull()->min(1)->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    /**
     * No producer is implemented, so this checks only the schema's `MetricProducer` shape.
     *
     * @see https://github.com/open-telemetry/opentelemetry-configuration/blob/v1.0.0/schema/meter_provider.yaml
     */
    private static function validateProducer(mixed $producer): array
    {
        if (!is_array($producer) || count($producer) !== 1 || !is_string($name = array_key_first($producer))) {
            throw new InvalidArgumentException('Metric producer must have exactly one key, naming the producer');
        }
        $properties = $producer[$name];
        // Both YAML parsers give `{}` as `[]`, so only a non-empty list is known to be a sequence.
        if ($properties !== null && (!is_array($properties) || ($properties !== [] && array_is_list($properties)))) {
            throw new InvalidArgumentException(sprintf('Metric producer "%s" must be null or a map', $name));
        }
        if ($name === 'opencensus' && $properties) {
            throw new InvalidArgumentException('Metric producer "opencensus" takes no properties');
        }

        return $producer;
    }
}
