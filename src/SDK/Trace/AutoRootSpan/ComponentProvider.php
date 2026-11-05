<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\AutoRootSpan;

use OpenTelemetry\API\Configuration\Config\ComponentProvider as ComponentProviderInterface;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\InstrumentationConfiguration;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * @implements ComponentProviderInterface<InstrumentationConfiguration>
 */
class ComponentProvider implements ComponentProviderInterface
{
    #[\Override]
    public function createPlugin(array $properties, Context $context): InstrumentationConfiguration
    {
        return new AutoRootSpanConfiguration(
            enabled: $properties['enabled'] ?? false,
        );
    }

    #[\Override]
    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        return $builder->arrayNode('auto_root_span')
            ->canBeEnabled();
    }
}
