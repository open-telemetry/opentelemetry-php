<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Internal\Node;

use Symfony\Component\Config\Definition\PrototypedArrayNode;

/**
 * @internal
 */
final class VariableNode extends \Symfony\Component\Config\Definition\VariableNode
{
    use NodeTrait;

    /**
     * A variable node takes any type, so a required one, such as a resource attribute value,
     * may be null. A list item still may not.
     */
    protected function acceptsNull(): bool
    {
        return !$this->parent instanceof PrototypedArrayNode;
    }
}
