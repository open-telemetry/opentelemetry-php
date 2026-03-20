<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Internal\NodeDefinition;

use OpenTelemetry\Config\SDK\Configuration\Internal\Node\EnumNode;
use Symfony\Component\Config\Definition\Builder\NodeParentInterface;

/**
 * @internal
 * @extends \Symfony\Component\Config\Definition\Builder\EnumNodeDefinition<NodeParentInterface|null>
 */
final class EnumNodeDefinition extends \Symfony\Component\Config\Definition\Builder\EnumNodeDefinition
{
    use NodeDefinitionTrait;

    #[\Override]
    protected function instantiateNode(): EnumNode
    {
        $node = parent::instantiateNode();

        return new EnumNode($this->name, $this->parent, $node->getValues(), $this->pathSeparator);
    }
}
