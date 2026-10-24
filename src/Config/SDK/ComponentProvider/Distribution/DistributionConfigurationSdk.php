<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Distribution;

use OpenTelemetry\API\Configuration\Config\ComponentPlugin;
use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\Config\SDK\Configuration\Validation;
use OpenTelemetry\Context\Propagation\MultiResponsePropagator;
use OpenTelemetry\Context\Propagation\ResponsePropagatorInterface;
use OpenTelemetry\SDK\Common\Distribution\DistributionConfiguration;
use OpenTelemetry\SDK\Common\Distribution\SdkDistribution;
use OpenTelemetry\SDK\Trace\SpanSuppression\NoopSuppressionStrategy\NoopSuppressionStrategy;
use OpenTelemetry\SDK\Trace\SpanSuppression\SpanSuppressionStrategy;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * @internal
 * @implements ComponentProvider<DistributionConfiguration>
 */
final class DistributionConfigurationSdk implements ComponentProvider
{
    /**
     * `span_suppression_strategy/development` is an appended component node, which
     * `addDefaultsIfNotSet()` does not populate, so it is absent rather than null when another key
     * is the only one configured.
     *
     * @param array{
     *     "span_suppression_strategy/development"?: ?ComponentPlugin<SpanSuppressionStrategy>,
     *     internal_metrics_enabled: bool,
     *     "response_propagator/development": array{
     *         composite_list: ?string,
     *         composite: list<ComponentPlugin<ResponsePropagatorInterface>>,
     *     },
     * } $properties
     */
    #[\Override]
    public function createPlugin(array $properties, Context $context): DistributionConfiguration
    {
        $responsePropagators = [];
        foreach ($properties['response_propagator/development']['composite'] as $plugin) {
            $responsePropagators[] = $plugin->create($context);
        }

        return new SdkDistribution(
            spanSuppressionStrategy: ($properties['span_suppression_strategy/development'] ?? null)?->create($context) ?? new NoopSuppressionStrategy(),
            internalMetricsEnabled: $properties['internal_metrics_enabled'],
            responsePropagator: ($responsePropagators === []) ? null : new MultiResponsePropagator($responsePropagators),
        );
    }

    #[\Override]
    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('opentelemetry_php/development');
        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->append($registry->component('span_suppression_strategy/development', SpanSuppressionStrategy::class))
                // Mirrors OTEL_PHP_INTERNAL_METRICS_ENABLED, which gates the same wiring in
                // environment-based configuration. No 1.0 schema key covers it.
                ->booleanNode('internal_metrics_enabled')->defaultFalse()->end()
                ->append($this->getResponsePropagatorConfig($registry, $builder))
            ->end()
        ;

        return $node;
    }

    /**
     * Response propagators are not in the specification. The shape follows the schema's
     * `propagator`, including `composite_list`, which mirrors OTEL_EXPERIMENTAL_RESPONSE_PROPAGATORS.
     */
    private function getResponsePropagatorConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('response_propagator/development');
        $node
            ->beforeNormalization()
                ->ifArray()
                ->then(static function (array $value): array {
                    $existing = [];
                    foreach ($value['composite'] ?? [] as $item) {
                        $existing[] = key($item);
                    }
                    foreach (explode(',', $value['composite_list'] ?? '') as $name) {
                        $name = trim($name);
                        if ($name !== '' && $name !== 'none' && !in_array($name, $existing)) {
                            $value['composite'][][$name] = null;
                            $existing[] = $name;
                        }
                    }

                    unset($value['composite_list']);

                    return $value;
                })
            ->end();

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('composite_list')->defaultNull()->validate()->always(Validation::ensureString())->end()->end()
                ->append($registry->componentList('composite', ResponsePropagatorInterface::class))
            ->end()
        ;

        return $node;
    }
}
