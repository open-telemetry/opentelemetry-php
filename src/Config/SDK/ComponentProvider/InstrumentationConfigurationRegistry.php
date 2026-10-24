<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\ComponentProvider;

use OpenTelemetry\API\Configuration\Config\ComponentPlugin;
use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Config\ComponentProviderRegistry;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\ConfigurationRegistry;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\InstrumentationConfiguration;
use OpenTelemetry\API\Instrumentation\Configuration\General\HttpConfig;
use OpenTelemetry\Config\SDK\ComponentProvider\Instrumentation\General\HttpConfigProvider;
use OpenTelemetry\Config\SDK\ComponentProvider\Instrumentation\General\SemconvConfig;
use OpenTelemetry\Config\SDK\Configuration\IgnoresUnknownProviders;
use OpenTelemetry\Config\SDK\Configuration\Validation;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * Root for the `instrumentation/development` node, processed separately from {@see OpenTelemetrySdk}
 * even though both describe the same document.
 *
 * The separation means a node that does not validate costs the instrumentation config and
 * nothing else, leaving the SDK configured. `/development` is exempt from the file format's
 * versioning guarantees, so this node is the part of the document most likely to disagree with
 * what we can parse. Appended to the SDK root, one bad header list would be fatal to tracing.
 * `ignoreExtraKeys()` and {@see IgnoresUnknownProviders} tolerate unknown keys and unregistered
 * providers, not a known node that fails type validation.
 *
 * The reverse holds only for {@see \OpenTelemetry\Config\SDK\ConfigProvider}.
 * {@see \OpenTelemetry\Config\SDK\Configuration::parseFile()} processes the SDK root first, so an
 * invalid SDK root costs its callers both.
 *
 * @internal
 * @todo Append `instrumentation/development` to {@see OpenTelemetrySdk} and remove this provider
 *       once the node drops its `/development` suffix and joins the versioned schema. Merging
 *       sooner without losing the independent failure needs a variable node that runs this schema
 *       through a nested processor in its validation closure, as
 *       {@see \OpenTelemetry\Config\SDK\Configuration\Internal\ComponentProviderRegistry::process()}
 *       does. That trades a declared schema for a hand-rolled closure to save one tree walk.
 * @implements ComponentProvider<ConfigurationRegistry>
 */
class InstrumentationConfigurationRegistry implements ComponentProvider
{
    /**
     * @param array{
     *     "instrumentation/development"?: array{
     *         php?: array<string, ComponentPlugin<?InstrumentationConfiguration>>,
     *         general?: array{http?: array}
     *     }
     * } $properties
     */
    #[\Override]
    public function createPlugin(array $properties, Context $context): ConfigurationRegistry
    {
        $instrumentation = $properties['instrumentation/development'] ?? [];

        $configurationRegistry = new ConfigurationRegistry();
        foreach ($instrumentation['php'] ?? [] as $plugin) {
            if (($configuration = $plugin->create($context)) !== null) {
                $configurationRegistry->add($configuration);
            }
        }
        if (($http = $instrumentation['general']['http'] ?? null) !== null) {
            $configurationRegistry->add(new HttpConfig($http));
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
                ->append($this->getInstrumentationNodeConfig($registry, $builder))
            ->end()
        ;

        return $root;
    }

    private function getInstrumentationNodeConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('instrumentation/development');
        $node
            // Unknown keys are dropped rather than rejected throughout this node: the other
            // languages' keys are siblings of `php`, and `/development` is exempt from upstream's
            // versioning guarantees, so a key added in a minor release must not take the SDK down.
            // ignoreExtraKeys() only covers direct children, so every nested array node declared
            // under here has to repeat it for that to hold at depth.
            ->ignoreExtraKeys()
            // A portable config naming an instrumentation this SDK does not ship should warn and
            // skip, matching the schema's open per-language node.
            ->append($registry->componentMap('php', InstrumentationConfiguration::class)
                ->attribute(IgnoresUnknownProviders::ATTRIBUTE, true))
            ->append($this->getGeneralConfig($registry, $builder))
        ;

        return $node;
    }

    /**
     * `ExperimentalGeneralInstrumentation`: fixed properties, not a component map, because a scalar
     * sibling such as `stability_opt_in_list` cannot resolve to a provider. Everything but `http`
     * is validated and discarded, since nothing in this SDK reads it yet.
     */
    private function getGeneralConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('general');
        $node
            ->ignoreExtraKeys()
            ->children()
                ->append((new HttpConfigProvider())->getConfig($registry, $builder))
                ->append($this->getSemconvOnlyConfig('code', $builder))
                ->append($this->getSemconvOnlyConfig('db', $builder))
                ->append($this->getSemconvOnlyConfig('gen_ai', $builder))
                ->append($this->getSemconvOnlyConfig('messaging', $builder))
                ->append($this->getSemconvOnlyConfig('rpc', $builder))
                ->append($this->getSanitizationConfig($builder))
                ->scalarNode('stability_opt_in_list')
                    ->defaultNull()
                    ->validate()->always(Validation::ensureString())->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    private function getSemconvOnlyConfig(string $name, NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode($name);
        $node
            ->ignoreExtraKeys()
            ->children()
                ->append(SemconvConfig::node($builder))
            ->end()
        ;

        return $node;
    }

    private function getSanitizationConfig(NodeBuilder $builder): ArrayNodeDefinition
    {
        $node = $builder->arrayNode('sanitization');
        $node
            ->ignoreExtraKeys()
            ->children()
                ->arrayNode('url')
                    ->ignoreExtraKeys()
                    ->children()
                        ->arrayNode('sensitive_query_parameters')
                            ->scalarPrototype()->validate()->always(Validation::ensureString())->end()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }
}
