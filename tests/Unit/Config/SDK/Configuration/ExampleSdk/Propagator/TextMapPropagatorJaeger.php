<?php

declare(strict_types=1);

namespace ExampleSDK\ComponentProvider\Propagator;

use BadMethodCallException;
use OpenTelemetry\Config\SDK\Configuration\ComponentProvider;
use OpenTelemetry\Config\SDK\Configuration\ComponentProviderRegistry;
use OpenTelemetry\Config\SDK\Configuration\Context;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

final class TextMapPropagatorJaeger implements ComponentProvider
{

    /**
     * @param array{} $properties
     */
    public function createPlugin(array $properties, Context $context): TextMapPropagatorInterface
    {
        throw new BadMethodCallException('not implemented');
    }

    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        return $builder->arrayNode('jaeger');
    }
}
