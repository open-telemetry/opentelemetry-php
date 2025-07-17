<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Internal\Node;

final class PrototypedArrayNode extends \Symfony\Component\Config\Definition\PrototypedArrayNode
{
    use NodeTrait;

    private bool $defaultValueSet = false;
    private mixed $defaultValue_ = null;
    private bool $allowEmptyValue = true;

    public static function fromNode(\Symfony\Component\Config\Definition\PrototypedArrayNode $node): PrototypedArrayNode
    {
        $_node = new self($node->getName());
        foreach (get_object_vars($node) as $property => $value) {
            $_node->$property = $value;
        }

        return $_node;
    }

    #[\Override]
    public function setDefaultValue(mixed $value): void
    {
        $this->defaultValue_ = $value;
        $this->defaultValueSet = true;
    }

    #[\Override]
    public function hasDefaultValue(): bool
    {
        return $this->defaultValueSet || parent::hasDefaultValue();
    }

    #[\Override]
    public function getDefaultValue(): mixed
    {
        return $this->defaultValueSet
            ? $this->defaultValue_
            : parent::getDefaultValue();
    }

    public function setAllowEmptyValue(bool $boolean): void
    {
        $this->allowEmptyValue = $boolean;
    }
}
