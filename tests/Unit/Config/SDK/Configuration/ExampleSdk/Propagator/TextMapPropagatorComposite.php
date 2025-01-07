<?php

declare(strict_types=1);

namespace ExampleSDK\ComponentProvider\Propagator;

use BadMethodCallException;
use OpenTelemetry\Config\SDK\Configuration\ComponentPlugin;
use OpenTelemetry\Config\SDK\Configuration\ComponentProvider;
use OpenTelemetry\Config\SDK\Configuration\ComponentProviderRegistry;
use OpenTelemetry\Config\SDK\Configuration\Context;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

final class TextMapPropagatorComposite implements ComponentProvider
{
    /**
     * @param list<ComponentPlugin<TextMapPropagatorInterface>> $properties
     */
    public function createPlugin(array $properties, Context $context): TextMapPropagatorInterface
    {
        throw new BadMethodCallException('not implemented');
    }

    public function getConfig(ComponentProviderRegistry $registry): ArrayNodeDefinition
    {
        return $registry->componentNames('composite', TextMapPropagatorInterface::class);
    }
}
