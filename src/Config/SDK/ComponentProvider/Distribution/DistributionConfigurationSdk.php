<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Distribution;

use OpenTelemetry\API\Configuration\Config\ComponentPlugin;
use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\SDK\Common\Distribution\DistributionConfiguration;
use OpenTelemetry\SDK\Common\Distribution\SdkDistribution;
use OpenTelemetry\SDK\Trace\SpanSuppression\NoopSuppressionStrategy\NoopSuppressionStrategy;
use OpenTelemetry\SDK\Trace\SpanSuppression\SpanSuppressionStrategy;
use Override;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
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
     * } $properties
     * @param Context $context
     * @return DistributionConfiguration
     */
    #[Override]
    public function createPlugin(array $properties, Context $context): DistributionConfiguration
    {
        return new SdkDistribution(
            spanSuppressionStrategy: ($properties['span_suppression_strategy/development'] ?? null)?->create($context) ?? new NoopSuppressionStrategy(),
            internalMetricsEnabled: $properties['internal_metrics_enabled'],
        );
    }

    #[Override]
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
            ->end()
        ;

        return $node;
    }
}
