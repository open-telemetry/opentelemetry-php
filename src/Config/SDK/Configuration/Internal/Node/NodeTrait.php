<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Internal\Node;

/**
 * @internal
 */
trait NodeTrait
{
    protected function preNormalize(mixed $value): mixed
    {
        if ($value === null && $this->allowEmptyValue && $this->defaultValueSet) {
            $value = $this->defaultValue;
        }

        /** @noinspection PhpMultipleClassDeclarationsInspection */
        return parent::preNormalize($value);
    }

    protected function normalizeValue(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        /** @noinspection PhpMultipleClassDeclarationsInspection */
        return parent::normalizeValue($value);
    }

    protected function mergeValues(mixed $leftSide, mixed $rightSide): mixed
    {
        if (null === $rightSide) {
            return $leftSide;
        }
        if (null === $leftSide) {
            return $rightSide;
        }

        /** @noinspection PhpMultipleClassDeclarationsInspection */
        return parent::mergeValues($leftSide, $rightSide);
    }

    protected function validateType(mixed $value): void
    {
        if ($value === null && $this->allowEmptyValue) {
            return;
        }

        /** @noinspection PhpMultipleClassDeclarationsInspection */
        parent::validateType($value);
    }

    public function finalizeValue(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        /** @noinspection PhpMultipleClassDeclarationsInspection */
        return parent::finalizeValue($value);
    }

    protected function isValueEmpty(mixed $value): bool
    {
        return $value === null;
    }
}
