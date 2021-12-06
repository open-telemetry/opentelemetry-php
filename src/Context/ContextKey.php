<?php

declare(strict_types=1);

namespace OpenTelemetry\Context;

class ContextKey
{
    private ?string $name;

    public function __construct(?string $name=null)
    {
        $this->name = $name;
    }

    public function name(): ?string
    {
        return $this->name;
    }
}
