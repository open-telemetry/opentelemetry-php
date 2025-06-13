<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Configuration\Config;

use function class_alias;
use OpenTelemetry\API\Configuration\Context;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * A component provider is responsible for interpreting configuration and returning an
 * implementation of a particular type.
 *
 * @template T
 */
interface ComponentProvider
{
    /**
     * @param array $properties properties provided for this component provider
     * @param Context $context context that should be used to resolve component plugins
     * @return T created component, typehint has to specify the component type that is
     *         provided by this component provider
     *
     * @see ComponentPlugin::create()
     */
    public function createPlugin(array $properties, Context $context): mixed;

    /**
     * Returns an array node describing the properties of this component provider.
     *
     * @param ComponentProviderRegistry $registry registry containing all available component providers
     * @param NodeBuilder $builder node builder used to create configuration nodes
     * @return ArrayNodeDefinition array node describing the properties
     *
     * @see ComponentProviderRegistry::component()
     * @see ComponentProviderRegistry::componentList()
     * @see ComponentProviderRegistry::componentNames()
     */
    public function getConfig(ComponentProviderRegistry $registry, NodeBuilder $builder): ArrayNodeDefinition;
}

/** @phpstan-ignore-next-line @phan-suppress-next-line PhanUndeclaredClassReference */
class_alias(ComponentProvider::class, \OpenTelemetry\Config\SDK\Configuration\ComponentProvider::class);
