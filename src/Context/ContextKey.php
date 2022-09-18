<?php

declare(strict_types=1);

namespace OpenTelemetry\Context;

/**
 * @internal
 */
final class ContextKey implements ContextKeyInterface
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
