<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Trace;

use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ComposableAlwaysOff;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ComposableSamplerInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * @internal
 * @experimental
 * @implements ComponentProvider<ComposableSamplerInterface>
 */
final class ComposableSamplerAlwaysOff implements ComponentProvider
{
    /**
     * @param array{} $properties
     */
    #[\Override]
    public function createPlugin(array $properties, Context $context): ComposableSamplerInterface
    {
        return new ComposableAlwaysOff();
    }

    #[\Override]
    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        return $builder->arrayNode('always_off');
    }
}
