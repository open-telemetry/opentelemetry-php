<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use OpenTelemetry\API\Trace as API;

class Attribute implements API\AttributeInterface
{
    private $key;
    private $value;

    public function __construct(string $key, $value)
    {
        // todo: validate key and value
        $this->key = $key;
        $this->value = $value;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getValue()
    {
        /**
        *
        * @return bool|int|float|string|list<int>|list<float>
        *
        */
        return $this->value;
    }
}
