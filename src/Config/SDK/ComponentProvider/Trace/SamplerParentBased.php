<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Trace;

use OpenTelemetry\API\Configuration\Config\ComponentPlugin;
use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOffSampler;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\Sampler\ParentBased;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * @implements ComponentProvider<SamplerInterface>
 */
final class SamplerParentBased implements ComponentProvider
{
    /**
     * @param array{
     *     root: ComponentPlugin<SamplerInterface>,
     *     remote_parent_sampled: ?ComponentPlugin<SamplerInterface>,
     *     remote_parent_not_sampled: ?ComponentPlugin<SamplerInterface>,
     *     local_parent_sampled: ?ComponentPlugin<SamplerInterface>,
     *     local_parent_not_sampled: ?ComponentPlugin<SamplerInterface>,
     * } $properties
     */
    public function createPlugin(array $properties, Context $context): SamplerInterface
    {
        return new ParentBased(
            root: $properties['root']->create($context),
            remoteParentSampler: ($properties['remote_parent_sampled'] ?? null)?->create($context) ?? new AlwaysOnSampler(),
            remoteParentNotSampler: ($properties['remote_parent_not_sampled'] ?? null)?->create($context) ?? new AlwaysOffSampler(),
            localParentSampler: ($properties['local_parent_sampled'] ?? null)?->create($context) ?? new AlwaysOnSampler(),
            localParentNotSampler: ($properties['local_parent_not_sampled'] ?? null)?->create($context) ?? new AlwaysOffSampler(),
        );
    }

    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('parent_based');
        $node
            ->children()
                ->append($registry->component('root', SamplerInterface::class)->isRequired())
                ->append($registry->component('remote_parent_sampled', SamplerInterface::class))
                ->append($registry->component('remote_parent_not_sampled', SamplerInterface::class))
                ->append($registry->component('local_parent_sampled', SamplerInterface::class))
                ->append($registry->component('local_parent_not_sampled', SamplerInterface::class))
            ->end()
        ;

        return $node;
    }
}
