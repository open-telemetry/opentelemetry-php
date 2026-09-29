<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Integration\Config\ComponentProvider\Trace;

use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\SDK\Trace\Sampler\TraceIdRatioBasedSampler;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * Unlike the other providers here this one is not a no-op: `probability/development` describes
 * consistent probability sampling via a 56-bit threshold, which is what
 * {@see TraceIdRatioBasedSampler} already implements, so the ratio is honoured.
 *
 * It stays in the test suite rather than the SDK only because the schema key is still
 * `/development`; promoting it to `src/` would be a behavioural commitment to an unstable key.
 *
 * @implements ComponentProvider<SamplerInterface>
 */
final class SamplerProbability implements ComponentProvider
{
    /**
     * @param array{
     *     ratio: float,
     * } $properties
     */
    #[\Override]
    public function createPlugin(array $properties, Context $context): SamplerInterface
    {
        return new TraceIdRatioBasedSampler(
            probability: $properties['ratio'],
        );
    }

    #[\Override]
    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('probability/development');
        $node
            ->children()
                ->floatNode('ratio')->min(0)->max(1)->defaultValue(1.0)->end()
            ->end()
        ;

        return $node;
    }
}
