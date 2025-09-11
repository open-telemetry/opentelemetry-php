<?php

declare(strict_types=1);

namespace ExampleSDK\ComponentProvider\Propagator;

use BadMethodCallException;
use OpenTelemetry\API\Configuration\Config\ComponentPlugin;
use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\Context\Propagation\ResponsePropagatorInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

final class ResponsePropagatorServerTiming implements ComponentProvider
{
    /**
     * @param ComponentPlugin $properties
     */
    #[\Override]
    public function createPlugin(array $properties, Context $context): ResponsePropagatorInterface
    {
        throw new BadMethodCallException('not implemented');
    }

    #[\Override]
    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        return $registry->componentNames('servertiming', ResponsePropagatorInterface::class);
    }
}
