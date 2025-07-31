<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Attribute;

class AttributeValidator implements AttributeValidatorInterface
{
    private const PRIMITIVES = [
        'string',
        'integer',
        'double',
        'boolean',
    ];
    private const NUMERICS = [
        'double',
        'integer',
    ];

    /**
     * Validate whether a value is a primitive, or a homogeneous array of primitives (treating int/double as equivalent).
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.21.0/specification/common/README.md#attribute
     */
    #[\Override]
    public function validate($value): bool
    {
        if (is_array($value)) {
            return $this->validateArray($value);
        }

        return in_array(gettype($value), self::PRIMITIVES);
    }

    private function validateArray(array $value): bool
    {
        if ($value === []) {
            return true;
        }
        $type = gettype(reset($value));
        if (!in_array($type, self::PRIMITIVES)) {
            return false;
        }
        foreach ($value as $v) {
            if (in_array(gettype($v), self::NUMERICS) && in_array($type, self::NUMERICS)) {
                continue;
            }
            if (gettype($v) !== $type) {
                return false;
            }
        }

        return true;
    }

    #[\Override]
    public function getInvalidMessage(): string
    {
        return 'attribute with non-primitive or non-homogeneous array of primitives dropped';
    }
}
