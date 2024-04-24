<?php declare(strict_types=1);
namespace OpenTelemetry\Example;

use OpenTelemetry\API\Instrumentation\AutoInstrumentation\InstrumentationConfiguration;
use OpenTelemetry\Config\SDK\Configuration\ComponentProvider;
use OpenTelemetry\Config\SDK\Configuration\ComponentProviderRegistry;
use OpenTelemetry\Config\SDK\Configuration\Context;
use OpenTelemetry\Config\SDK\Configuration\Validation;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * @implements ComponentProvider<InstrumentationConfiguration>
 */
final class ExampleConfigProvider implements ComponentProvider {

    /**
     * @param array{
     *     span_name: string,
     *     enabled: bool,
     * } $properties
     */
    public function createPlugin(array $properties, Context $context): InstrumentationConfiguration {
        return new ExampleConfig(
            spanName: $properties['span_name'],
            enabled: $properties['enabled'],
        );
    }

    public function getConfig(ComponentProviderRegistry $registry): ArrayNodeDefinition {
        $root = new ArrayNodeDefinition('example_instrumentation');
        $root
            ->children()
                ->scalarNode('span_name')->isRequired()->validate()->always(Validation::ensureString())->end()->end()
            ->end()
            ->canBeDisabled()
        ;

        return $root;
    }
}
