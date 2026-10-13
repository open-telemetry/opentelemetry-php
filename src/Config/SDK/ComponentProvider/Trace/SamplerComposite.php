<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Trace;

use OpenTelemetry\API\Configuration\Config\ComponentPlugin;
use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ComposableInterface;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ConsistentSampler;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * Wraps a composable (consistent probability) sampler as a {@see SamplerInterface} (the spec's CompositeSampler).
 *
 * @experimental
 * @implements ComponentProvider<SamplerInterface>
 */
final class SamplerComposite implements ComponentProvider
{
    /**
     * @param list<ComponentPlugin<ComposableInterface>> $properties
     */
    #[\Override]
    public function createPlugin(array $properties, Context $context): SamplerInterface
    {
        if (\count($properties) !== 1) {
            throw new \InvalidArgumentException('composite/development must have exactly one composable sampler defined');
        }

        $sampler = $properties[0]->create($context);

        // Built-in composable samplers already are samplers; any other composable sampler is wrapped
        return $sampler instanceof SamplerInterface ? $sampler : ConsistentSampler::composite($sampler);
    }

    #[\Override]
    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $registry->componentMap('composite/development', ComposableInterface::class);
        $node->isRequired();

        return $node;
    }
}
