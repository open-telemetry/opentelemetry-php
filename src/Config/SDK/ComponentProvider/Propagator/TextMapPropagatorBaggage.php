<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider\Propagator;

use OpenTelemetry\API\Baggage\Propagation\BaggagePropagator;
use OpenTelemetry\Config\SDK\Configuration\ComponentProvider;
use OpenTelemetry\Config\SDK\Configuration\ComponentProviderRegistry;
use OpenTelemetry\Config\SDK\Configuration\Context;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * @implements ComponentProvider<TextMapPropagatorInterface>
 */
final class TextMapPropagatorBaggage implements ComponentProvider
{

    /**
     * @param array{} $properties
     */
    public function createPlugin(array $properties, Context $context): TextMapPropagatorInterface
    {
        return BaggagePropagator::getInstance();
    }

    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        return $builder->arrayNode('baggage');
    }
}
