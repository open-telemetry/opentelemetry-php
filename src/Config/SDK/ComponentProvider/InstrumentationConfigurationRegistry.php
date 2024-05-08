<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider;

use OpenTelemetry\API\Instrumentation\AutoInstrumentation\ConfigurationRegistry;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\InstrumentationConfiguration;
use OpenTelemetry\Config\SDK\Configuration\ComponentPlugin;
use OpenTelemetry\Config\SDK\Configuration\ComponentProvider;
use OpenTelemetry\Config\SDK\Configuration\ComponentProviderRegistry;
use OpenTelemetry\Config\SDK\Configuration\Context;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * @internal
 *
 * @implements ComponentProvider<ConfigurationRegistry>
 */
class InstrumentationConfigurationRegistry implements ComponentProvider
{
    /**
     * @param array{
     *     config: list<ComponentPlugin<InstrumentationConfiguration>>,
     * } $properties
     */
    public function createPlugin(array $properties, Context $context): ConfigurationRegistry
    {
        $configurationRegistry = new ConfigurationRegistry();
        foreach ($properties['config'] as $configuration) {
            $configurationRegistry->add($configuration->create($context));
        }

        return $configurationRegistry;
    }

    public function getConfig(ComponentProviderRegistry $registry): ArrayNodeDefinition
    {
        $root = new ArrayNodeDefinition('instrumentation');
        $root
            ->children()
            // TODO add disabled_instrumentations arrayNode to allow disabling specific instrumentation classes?
            ->append($registry->componentList('config', InstrumentationConfiguration::class))
            ->end()
        ;

        return $root;
    }
}
