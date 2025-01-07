<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Internal;

use function assert;
use Symfony\Component\Config\Definition\ArrayNode;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\NodeInterface;

/**
 * @internal
 */
final class ArrayNodeDefaultNullDefinition extends ArrayNodeDefinition
{
    protected function createNode(): NodeInterface
    {
        $node = parent::createNode();
        assert($node instanceof ArrayNode);

        return ArrayNodeDefaultNull::fromNode($node);
    }
}
