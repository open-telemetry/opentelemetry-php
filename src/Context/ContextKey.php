<?php

declare(strict_types=1);

namespace OpenTelemetry\Context;

/**
 * @internal
 * @psalm-suppress MissingTemplateParam
 */
final readonly class ContextKey implements ContextKeyInterface
{
    public function __construct(private ?string $name = null)
    {
    }

    public function name(): ?string
    {
        return $this->name;
    }
}
