<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Trace;

use OpenTelemetry\API\Configuration\Config\ComponentPlugin;
use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ComposableSamplerInterface;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\CompositeSampler;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * Wraps a composable sampler in a {@see CompositeSampler}.
 *
 * @experimental
 * @implements ComponentProvider<SamplerInterface>
 */
final class SamplerComposite implements ComponentProvider
{
    /**
     * @param list<ComponentPlugin<ComposableSamplerInterface>> $properties
     */
    #[\Override]
    public function createPlugin(array $properties, Context $context): SamplerInterface
    {
        return new CompositeSampler($properties[0]->create($context));
    }

    #[\Override]
    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $registry->componentMap('composite/development', ComposableSamplerInterface::class);
        $node->isRequired();
        // The schema requires exactly one composable sampler (minProperties: 1, maxProperties: 1)
        $node->validate()->always(static function (array $value): array {
            if (\count($value) !== 1) {
                throw new \InvalidArgumentException(\sprintf('Composite sampler must have exactly one composable sampler defined, got %d', \count($value)));
            }

            return $value;
        });

        return $node;
    }
}
