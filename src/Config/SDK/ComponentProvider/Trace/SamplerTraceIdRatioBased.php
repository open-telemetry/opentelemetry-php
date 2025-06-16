<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Trace;

use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\SDK\Trace\Sampler\TraceIdRatioBasedSampler;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * @implements ComponentProvider<SamplerInterface>
 */
final class SamplerTraceIdRatioBased implements ComponentProvider
{
    /**
     * @param array{
     *     ratio: float,
     * } $properties
     */
    public function createPlugin(array $properties, Context $context): SamplerInterface
    {
        return new TraceIdRatioBasedSampler(
            probability: $properties['ratio'],
        );
    }

    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('trace_id_ratio_based');
        $node
            ->children()
                ->floatNode('ratio')->min(0)->max(1)->isRequired()->end()
            ->end()
        ;

        return $node;
    }
}
