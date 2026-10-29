<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Internal\Node;

use Symfony\Component\Config\Definition\Exception\InvalidTypeException;
use Symfony\Component\Config\Definition\PrototypedArrayNode;

/**
 * @internal
 */
trait NodeTrait
{
    protected function preNormalize(mixed $value): mixed
    {
        if ($value === null && $this->allowEmptyValue && $this->hasDefaultValue() && $this->acceptsNull()) {
            $value = $this->getDefaultValue();
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

    /**
     * A required property or a list item must have a value, unless its schema type includes "null".
     */
    protected function acceptsNull(): bool
    {
        return !$this->required && !$this->parent instanceof PrototypedArrayNode;
    }

    protected function validateType(mixed $value): void
    {
        if ($value === null && !$this->acceptsNull()) {
            $ex = new InvalidTypeException(\sprintf('The path "%s" cannot be null.', $this->getPath()));
            if ($hint = $this->getInfo()) {
                $ex->addHint($hint);
            }
            $ex->setPath($this->getPath());

            throw $ex;
        }
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
        return $value === null || $value === '';
    }
}
