<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

/**
 * A registry of component providers.
 */
interface ComponentProviderRegistry
{

    /**
     * Creates a node to specify a component plugin.
     *
     * `$name: ?ComponentPlugin<$type>`
     *
     * ```
     * $name:
     *   provider1:
     *     property: value
     *     anotherProperty: value
     * ```
     *
     * @param string $name name of configuration node
     * @param string $type type of the component plugin
     */
    public function component(string $name, string $type): NodeDefinition;

    /**
     * Creates a node to specify a list of component plugin.
     *
     * `$name: list<ComponentPlugin<$type>>`
     *
     * ```
     * $name:
     *   provider1:
     *     property: value
     *     anotherProperty: value
     *   provider2:
     *     property: value
     *     anotherProperty: value
     * ```
     *
     * @param string $name name of configuration node
     * @param string $type type of the component plugin
     */
    public function componentList(string $name, string $type): ArrayNodeDefinition;

    /**
     * Creates a node to specify a list of component plugins represented as an array.
     *
     * `$name: list<ComponentPlugin<$type>>`
     *
     * ```
     * $name:
     * - provider1:
     *      property: value
     *      anotherProperty: value
     * - provider2:
     *      property: value
     *      anotherProperty: value
     * ```
     *
     * @param string $name name of configuration node
     * @param string $type type of the component plugin
     */
    public function componentArrayList(string $name, string $type): ArrayNodeDefinition;
    /**
     * Creates a node to specify a list of component plugin names.
     *
     * The providers cannot have required properties.
     *
     * `$name: list<ComponentPlugin<$type>>`
     *
     * ```
     * $name: [provider1, provider2]
     * ```
     *
     * @param string $name name of configuration node
     * @param string $type type of the component plugin
     */
    public function componentNames(string $name, string $type): ArrayNodeDefinition;
}
