<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Internal;

use function get_object_vars;
use Symfony\Component\Config\Definition\ArrayNode;

/**
 * @internal
 */
final class ArrayNodeDefaultNull extends ArrayNode
{

    public static function fromNode(ArrayNode $node): ArrayNodeDefaultNull
    {
        $defaultNull = new ArrayNodeDefaultNull($node->getName());
        foreach (get_object_vars($node) as $property => $value) {
            $defaultNull->$property = $value;
        }

        return $defaultNull;
    }

    public function hasDefaultValue(): bool
    {
        return true;
    }

    public function getDefaultValue(): mixed
    {
        return null;
    }
}
