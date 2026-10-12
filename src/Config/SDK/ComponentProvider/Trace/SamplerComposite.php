<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Trace;

use OpenTelemetry\API\Configuration\Config\ComponentPlugin;
use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ComposableInterface;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * Wraps a composable (consistent probability) sampler as a {@see SamplerInterface}.
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
        if (!$sampler instanceof SamplerInterface) {
            throw new \InvalidArgumentException(\sprintf('Composable sampler "%s" cannot be used as a sampler', \get_debug_type($sampler)));
        }

        return $sampler;
    }

    #[\Override]
    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $registry->componentMap('composite/development', ComposableInterface::class);
        $node->isRequired();

        return $node;
    }
}
