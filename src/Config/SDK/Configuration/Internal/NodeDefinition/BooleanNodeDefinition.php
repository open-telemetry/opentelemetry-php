<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Internal\NodeDefinition;

use OpenTelemetry\Config\SDK\Configuration\Internal\Node\BooleanNode;

/**
 * @internal
 */
final class BooleanNodeDefinition extends \Symfony\Component\Config\Definition\Builder\BooleanNodeDefinition
{
    use NodeDefinitionTrait;

    #[\Override]
    protected function instantiateNode(): BooleanNode
    {
        return new BooleanNode($this->name, $this->parent, $this->pathSeparator);
    }
}
