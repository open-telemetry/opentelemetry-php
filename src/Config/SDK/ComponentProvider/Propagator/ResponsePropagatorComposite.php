<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Propagator;

use OpenTelemetry\API\Configuration\Config\ComponentPlugin;
use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\Context\Propagation\MultiResponsePropagator;
use OpenTelemetry\Context\Propagation\ResponsePropagatorInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * @implements ComponentProvider<ResponsePropagatorInterface>
 */
final class ResponsePropagatorComposite implements ComponentProvider
{
    /**
     * @param list<ComponentPlugin<ResponsePropagatorInterface>> $properties
     */
    #[\Override]
    public function createPlugin(array $properties, Context $context): ResponsePropagatorInterface
    {
        $responsePropagators = [];
        foreach ($properties as $plugin) {
            $responsePropagators[] = $plugin->create($context);
        }

        return new MultiResponsePropagator($responsePropagators);
    }

    #[\Override]
    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        return $registry->componentNames('composite', ResponsePropagatorInterface::class);
    }
}
