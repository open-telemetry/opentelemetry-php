<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Internal\Node;

use function get_object_vars;
use OpenTelemetry\Config\SDK\Configuration\Internal\Normalization;
use OpenTelemetry\Config\SDK\Configuration\Internal\NormalizationsAware;

/**
 * @internal
 */
final class ArrayNode extends \Symfony\Component\Config\Definition\ArrayNode implements NormalizationsAware
{
    use NodeTrait {
        preNormalize as private preNormalizeNode;
    }

    private bool $defaultValueSet = false;
    private mixed $defaultValue = null;
    private bool $allowEmptyValue = true;
    /** @var list<Normalization> */
    private array $normalizations = [];

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

    #[\Override]
    public function setNormalizations(array $normalizations): void
    {
        $this->normalizations = $normalizations;
    }

    #[\Override]
    protected function preNormalize(mixed $value): mixed
    {
        foreach ($this->normalizations as $normalization) {
            $value = $normalization->applyToNode($this, $value);
        }

        return $this->preNormalizeNode($value);
    }
}
