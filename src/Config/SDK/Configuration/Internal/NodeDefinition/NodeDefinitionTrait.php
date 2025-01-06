<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Internal\NodeDefinition;

trait NodeDefinitionTrait
{
    protected $allowEmptyValue = true; //@todo type bool from symfony/config 7.2

    public function cannotBeEmpty(): static
    {
        $this->allowEmptyValue = false;

        return $this;
    }

    public function defaultValue(mixed $value): static
    {
        /**
         * If a property has a default value defined (i.e. is _not_ required) and is
         * missing or present but null, Create MUST ensure the SDK component is configured
         * with the default value.
         **/
        $this->validate()->ifNull()->then(static function () use ($value): mixed {
            return $value;
        });

        return parent::defaultValue($value);
    }
}
