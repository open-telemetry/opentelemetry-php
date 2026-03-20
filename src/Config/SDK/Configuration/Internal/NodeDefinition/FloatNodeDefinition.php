<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Internal\NodeDefinition;

use OpenTelemetry\Config\SDK\Configuration\Internal\Node\FloatNode;

/**
 * @internal
 * @extends \Symfony\Component\Config\Definition\Builder\FloatNodeDefinition<\Symfony\Component\Config\Definition\Builder\NodeParentInterface|null>
 */
final class FloatNodeDefinition extends \Symfony\Component\Config\Definition\Builder\FloatNodeDefinition
{
    use NodeDefinitionTrait;

    #[\Override]
    protected function instantiateNode(): FloatNode
    {
        return new FloatNode($this->name, $this->parent, $this->min, $this->max, $this->pathSeparator);
    }
}
