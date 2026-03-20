<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Internal\NodeDefinition;

use OpenTelemetry\Config\SDK\Configuration\Internal\Node\IntegerNode;
use Symfony\Component\Config\Definition\Builder\NodeParentInterface;

/**
 * @internal
 * @extends \Symfony\Component\Config\Definition\Builder\IntegerNodeDefinition<NodeParentInterface|null>
 */
final class IntegerNodeDefinition extends \Symfony\Component\Config\Definition\Builder\IntegerNodeDefinition
{
    use NodeDefinitionTrait;

    #[\Override]
    protected function instantiateNode(): IntegerNode
    {
        return new IntegerNode($this->name, $this->parent, $this->min, $this->max, $this->pathSeparator);
    }
}
