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
     * @param array{
     *     "span_suppression_strategy/development": ?ComponentPlugin<SpanSuppressionStrategy>,
     * } $properties
     * @param Context $context
     * @return DistributionConfiguration
     */
    #[Override]
    public function createPlugin(array $properties, Context $context): DistributionConfiguration
    {
        return new SdkDistribution(
            spanSuppressionStrategy: $properties['span_suppression_strategy/development']?->create($context) ?? new NoopSuppressionStrategy(),
        );
    }

    #[Override]
    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('opentelemetry_php/development');
        $node
            ->children()
                ->append($registry->component('span_suppression_strategy/development', SpanSuppressionStrategy::class))
            ->end()
        ;

        return $node;
    }
}
