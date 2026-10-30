<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Internal\Node;

use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_FLOAT;
use function filter_var;
use function is_string;

/**
 * @internal
 */
final class FloatNode extends \Symfony\Component\Config\Definition\FloatNode
{
    use NodeTrait {
        preNormalize as private preNormalizeNode;
    }

    #[\Override]
    protected function preNormalize(mixed $value): mixed
    {
        if (is_string($value)) {
            $value = filter_var($value, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE) ?? $value;
        }

        return $this->preNormalizeNode($value);
    }
}
