<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Trace;

use Nevay\OTelSDK\Configuration\ComponentPlugin;
use Nevay\OTelSDK\Configuration\ComponentProvider;
use Nevay\OTelSDK\Configuration\ComponentProviderRegistry;
use Nevay\OTelSDK\Configuration\Context;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOffSampler;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\Sampler\ParentBased;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

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
            remoteParentSampler: $properties['remote_parent_sampled']?->create($context) ?? new AlwaysOnSampler(),
            remoteParentNotSampler: $properties['remote_parent_not_sampled']?->create($context) ?? new AlwaysOffSampler(),
            localParentSampler: $properties['local_parent_sampled']?->create($context) ?? new AlwaysOnSampler(),
            localParentNotSampler: $properties['local_parent_not_sampled']?->create($context) ?? new AlwaysOffSampler(),
        );
    }

    public function getConfig(ComponentProviderRegistry $registry): ArrayNodeDefinition
    {
        $node = new ArrayNodeDefinition('parent_based');
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
