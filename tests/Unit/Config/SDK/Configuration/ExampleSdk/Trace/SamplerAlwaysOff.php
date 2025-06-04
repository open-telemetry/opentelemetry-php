<?php

declare(strict_types=1);

namespace ExampleSDK\ComponentProvider\Trace;

use BadMethodCallException;
use ExampleSDK\Trace\Sampler;
use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

final class SamplerAlwaysOff implements ComponentProvider
{
    /**
     * @param array{} $properties
     */
    public function createPlugin(array $properties, Context $context): Sampler
    {
        throw new BadMethodCallException('not implemented');
    }

    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        return $builder->arrayNode('always_off');
    }
}
