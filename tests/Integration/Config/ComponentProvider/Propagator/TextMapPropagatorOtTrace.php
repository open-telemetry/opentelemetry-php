<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Integration\Config\ComponentProvider\Propagator;

use OpenTelemetry\Config\SDK\Configuration\ComponentProvider;
use OpenTelemetry\Config\SDK\Configuration\ComponentProviderRegistry;
use OpenTelemetry\Config\SDK\Configuration\Context;
use OpenTelemetry\Context\Context as Ctx;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\Context\Propagation\PropagationGetterInterface;
use OpenTelemetry\Context\Propagation\PropagationSetterInterface;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * @implements ComponentProvider<TextMapPropagatorInterface>
 */
final class TextMapPropagatorOtTrace implements ComponentProvider
{
    public function createPlugin(array $properties, Context $context): TextMapPropagatorInterface
    {
        return new class() implements TextMapPropagatorInterface {
            public function fields(): array
            {
                return [];
            }

            public function inject(mixed &$carrier, ?PropagationSetterInterface $setter = null, ?ContextInterface $context = null): void
            {
                //no-op
            }

            public function extract($carrier, ?PropagationGetterInterface $getter = null, ?ContextInterface $context = null): ContextInterface
            {
                return $context ?? Ctx::getCurrent();
            }
        };
    }

    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        return $builder->arrayNode('ottrace');
    }
}
