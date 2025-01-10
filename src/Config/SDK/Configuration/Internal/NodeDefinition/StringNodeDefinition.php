<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Internal\NodeDefinition;

use OpenTelemetry\Config\SDK\Configuration\Internal\Node\StringNode;
use Symfony\Component\Config\Definition\ScalarNode;

/**
 * @internal
 */
final class StringNodeDefinition extends \Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition
{
    use NodeDefinitionTrait;

    protected function instantiateNode(): ScalarNode
    {
        return new StringNode($this->name, $this->parent, $this->pathSeparator);
    }
}
