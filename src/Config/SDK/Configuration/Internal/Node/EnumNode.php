<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Internal\Node;

/**
 * @internal
 */
final class EnumNode extends \Symfony\Component\Config\Definition\EnumNode
{
    use NodeTrait {
        acceptsNull as private acceptsNullNode;
    }

    /**
     * Listing null among the values makes the enum nullable, as `type: [string, "null"]` does.
     */
    protected function acceptsNull(): bool
    {
        return in_array(null, $this->getValues(), true) || $this->acceptsNullNode();
    }
}
