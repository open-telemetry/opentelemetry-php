<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Internal\NodeDefinition;

use OpenTelemetry\Config\SDK\Configuration\Internal\Node\ScalarNode;

/**
 * @internal
 */
final class ScalarNodeDefinition extends \Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition
{
    use NodeDefinitionTrait;

    protected function instantiateNode(): ScalarNode
    {
        return new ScalarNode($this->name, $this->parent, $this->pathSeparator);
    }
}
