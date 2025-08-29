<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider;

use OpenTelemetry\API\Configuration\Config\ComponentPlugin;
use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use Opefinal nTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\ConfigurationRegistry;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\GeneralInstrumentationConfiguration;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\InstrumentationConfiguration;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * @internal
 * @todo In a future release, when all instrumentations use SPI (and not autoload.files), this could be moved into {@see OpenTelemetrySdk}>
 * @implements ComponentProvider<ConfigurationRegistry>
 */
class InstrumentationConfigurationRegistry implements ComponentProvider
{
    /**
     * @param array{
     *     "instrumentation/development": array{
     *         php: list<ComponentPlugin<InstrumentationConfiguration>>,
     *         general: list<ComponentPlugin<GeneralInstrumentationConfiguration>>
     *     }
     * } $properties
     */
    #[\Override]
    public function createPlugin(array $properties, Context $context): ConfigurationRegistry
    {
        $configurationRegistry = new ConfigurationRegistry();
        /** @phpstan-ignore-next-line */
        foreach ($properties['instrumentation/development']['php'] ?? [] as $configuration) {
            $configurationRegistry->add($configuration->create($context));
        }
        /** @phpstan-ignore-next-line */
        foreach ($properties['instrumentation/development']['general'] ?? [] as $configuration) {
            $configurationRegistry->add($configuration->create($context));
        }

        return $configurationRegistry;
    }

    #[\Override]
    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $root = $builder->arrayNode('open_telemetry');
        $root
            ->ignoreExtraKeys()
            ->children()
                ->arrayNode('instrumentation/development')
                    ->ignoreExtraKeys()
                    ->append($registry->componentMap('php', InstrumentationConfiguration::class))
                    ->append($registry->componentMap('general', GeneralInstrumentationConfiguration::class))
                ->end()
            ->end()
        ;

        return $root;
    }
}
