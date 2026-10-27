<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Internal\Node;

use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_BOOLEAN;
use function filter_var;
use function is_string;
use function trim;

/**
 * @internal
 */
final class BooleanNode extends \Symfony\Component\Config\Definition\BooleanNode
{
    use NodeTrait {
        preNormalize as private preNormalizeNode;
    }

    #[\Override]
    protected function preNormalize(mixed $value): mixed
    {
        // filter_var() reads a blank string as false
        if (is_string($value) && trim($value) !== '') {
            $value = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $value;
        }

        return $this->preNormalizeNode($value);
    }
}
