<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Internal\Node;

use function is_string;
use function trim;

/**
 * Strings are read with the YAML 1.2 core schema, so `on`, `yes` and `1` are not booleans.
 *
 * @internal
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.61.0/specification/configuration/data-model.md#yaml-file-format
 */
final class BooleanNode extends \Symfony\Component\Config\Definition\BooleanNode
{
    use NodeTrait {
        preNormalize as private preNormalizeNode;
    }

    #[\Override]
    protected function preNormalize(mixed $value): mixed
    {
        if (is_string($value)) {
            $value = match (trim($value)) {
                'true', 'True', 'TRUE' => true,
                'false', 'False', 'FALSE' => false,
                default => $value,
            };
        }

        return $this->preNormalizeNode($value);
    }
}
