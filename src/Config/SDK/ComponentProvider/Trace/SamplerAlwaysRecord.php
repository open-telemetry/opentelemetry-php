<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Trace;

use OpenTelemetry\API\Configuration\Config\ComponentPlugin;
use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysRecordSampler;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * Sampler that upgrades DROP decisions to RECORD_ONLY, allowing span processors
 * to observe all spans without exporting them to backends.
 *
 * Wraps any root sampler. Spec key: always_record.
 *
 * @see https://github.com/open-telemetry/opentelemetry-configuration/blob/main/schema/tracer_provider.yaml
 *
 * @implements ComponentProvider<SamplerInterface>
 * @experimental
 */
final class SamplerAlwaysRecord implements ComponentProvider
{
    /**
     * @param array{
     *     root: ComponentPlugin<SamplerInterface>,
     * } $properties
     */
    #[\Override]
    public function createPlugin(array $properties, Context $context): SamplerInterface
    {
        return new AlwaysRecordSampler(
            root: $properties['root']->create($context),
        );
    }

    #[\Override]
    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('always_record');
        $node
            ->children()
                ->append($registry->component('root', SamplerInterface::class)->isRequired())
            ->end()
        ;

        return $node;
    }
}
