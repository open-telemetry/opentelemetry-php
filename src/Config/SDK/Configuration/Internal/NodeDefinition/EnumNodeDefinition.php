<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Internal\NodeDefinition;

use OpenTelemetry\Config\SDK\Configuration\Internal\Node\EnumNode;

/**
 * @internal
 */
final class EnumNodeDefinition extends \Symfony\Component\Config\Definition\Builder\EnumNodeDefinition
{
    use NodeDefinitionTrait;

    protected function instantiateNode(): EnumNode
    {
        $node = parent::instantiateNode();

        return new EnumNode($this->name, $this->parent, $node->getValues(), $this->pathSeparator);
    }
}
