<?php

/** @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection */

declare(strict_types=1);

namespace OpenTelemetry\Context;

use Fiber;

final class FiberBoundContextStorageScope implements ScopeInterface, ContextStorageScopeInterface
{
    private ContextStorageScopeInterface $scope;

    public function __construct(ContextStorageScopeInterface $scope)
    {
        $this->scope = $scope;
    }

    public function offsetExists($offset): bool
    {
        return $this->scope->offsetExists($offset);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->scope->offsetGet($offset);
    }

    public function offsetSet($offset, $value): void
    {
        $this->scope->offsetSet($offset, $value);
    }

    public function offsetUnset($offset): void
    {
        $this->scope->offsetUnset($offset);
    }

    public function context(): Context
    {
        return $this->scope->context();
    }

    public function detach(): int
    {
        $flags = $this->scope->detach();
        if ($this->scope[Fiber::class] !== Fiber::getCurrent()) {
            $flags |= ScopeInterface::INACTIVE;
        }

        return $flags;
    }
}
