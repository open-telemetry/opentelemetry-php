<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Internal\NodeDefinition;

trait NodeDefinitionTrait
{
    protected bool $allowEmptyValue = true;

    public function cannotBeEmpty(): static
    {
        $this->allowEmptyValue = false;

        return $this;
    }

    public function defaultValue(mixed $value): static
    {
        $this->validate()->ifNull()->then(static function () use ($value): mixed {
            return $value;
        });

        return parent::defaultValue($value);
    }
}
