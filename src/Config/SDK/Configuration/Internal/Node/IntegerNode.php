<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Internal\Node;

use const FILTER_FLAG_ALLOW_HEX;
use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_INT;
use function filter_var;
use function is_string;

/**
 * @internal
 */
final class IntegerNode extends \Symfony\Component\Config\Definition\IntegerNode
{
    use NodeTrait {
        preNormalize as private preNormalizeNode;
    }

    #[\Override]
    protected function preNormalize(mixed $value): mixed
    {
        if (is_string($value)) {
            $value = filter_var($value, FILTER_VALIDATE_INT, FILTER_FLAG_ALLOW_HEX | FILTER_NULL_ON_FAILURE) ?? $value;
        }

        return $this->preNormalizeNode($value);
    }
}
