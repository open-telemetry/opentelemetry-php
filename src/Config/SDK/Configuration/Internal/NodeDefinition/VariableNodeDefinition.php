<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Internal\NodeDefinition;

use OpenTelemetry\Config\SDK\Configuration\Internal\Node\VariableNode;

/**
 * @internal
 */
final class VariableNodeDefinition extends \Symfony\Component\Config\Definition\Builder\VariableNodeDefinition
{
    use NodeDefinitionTrait;

    #[\Override]
    protected function instantiateNode(): VariableNode
    {
        return new VariableNode($this->name, $this->parent, $this->pathSeparator);
    }
}
