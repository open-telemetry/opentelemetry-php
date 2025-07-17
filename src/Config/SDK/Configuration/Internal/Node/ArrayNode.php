<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Internal\Node;

use function get_object_vars;

/**
 * @internal
 */
final class ArrayNode extends \Symfony\Component\Config\Definition\ArrayNode
{
    use NodeTrait;

    private bool $defaultValueSet = false;
    private mixed $defaultValue = null;
    private bool $allowEmptyValue = true;

    public static function fromNode(\Symfony\Component\Config\Definition\ArrayNode $node): ArrayNode
    {
        $_node = new self($node->getName());
        foreach (get_object_vars($node) as $property => $value) {
            $_node->$property = $value;
        }

        return $_node;
    }

    public function setDefaultValue(mixed $value): void
    {
        $this->defaultValue = $value;
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
            ? $this->defaultValue
            : parent::getDefaultValue();
    }

    public function setAllowEmptyValue(bool $boolean): void
    {
        $this->allowEmptyValue = $boolean;
    }
}
