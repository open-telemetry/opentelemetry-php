<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider;

use OpenTelemetry\API\Instrumentation\AutoInstrumentation\ConfigurationRegistry;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\GeneralInstrumentationConfiguration;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\InstrumentationConfiguration;
use OpenTelemetry\Config\SDK\Configuration\ComponentPlugin;
use OpenTelemetry\Config\SDK\Configuration\ComponentProvider;
use OpenTelemetry\Config\SDK\Configuration\ComponentProviderRegistry;
use OpenTelemetry\Config\SDK\Configuration\Context;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * @internal
 * @todo In a future release, when all instrumentations use SPI (and not autoload.files), this could be moved into {@see OpenTelemetrySdk}>
 * @implements ComponentProvider<ConfigurationRegistry>
 */
class InstrumentationConfigurationRegistry implements ComponentProvider
{
    /**
     * @param array{
     *     instrumentation: array{
     *         php: list<ComponentPlugin<InstrumentationConfiguration>>,
     *         general: list<ComponentPlugin<InstrumentationConfiguration>>
     *     }
     * } $properties
     */
    public function createPlugin(array $properties, Context $context): ConfigurationRegistry
    {
        $configurationRegistry = new ConfigurationRegistry();
        /** @phpstan-ignore-next-line */
        foreach ($properties['instrumentation']['php'] ?? [] as $configuration) {
            $configurationRegistry->add($configuration->create($context));
        }
        /** @phpstan-ignore-next-line */
        foreach ($properties['instrumentation']['general'] ?? [] as $configuration) {
            $configurationRegistry->add($configuration->create($context));
        }

        return $configurationRegistry;
    }

    public function getConfig(ComponentProviderRegistry $registry): ArrayNodeDefinition
    {
        $root = new ArrayNodeDefinition('open_telemetry');
        $root
            ->ignoreExtraKeys()
            ->children()
                ->arrayNode('instrumentation')
                    ->ignoreExtraKeys()
                    ->children()
                        ->append($registry->componentList('php', InstrumentationConfiguration::class))
                        ->append($registry->componentList('general', GeneralInstrumentationConfiguration::class))
                    ->end()
                ->end()
            ->end()
        ;

        return $root;
    }
}
