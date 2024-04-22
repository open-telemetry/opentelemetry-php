<?php

declare(strict_types=1);

namespace OpenTelemetry\Context;

/**
 * @internal
 */
final class FiberBoundContextStorageExecutionAwareBC implements ContextStorageInterface, ExecutionContextAwareInterface
{
    private readonly FiberBoundContextStorage $storage;
    private ?ContextStorage $bc = null;

    public function __construct()
    {
        $this->storage = new FiberBoundContextStorage();
    }

    public function fork(int|string $id): void
    {
        $this->bcStorage()->fork($id);
    }

    public function switch(int|string $id): void
    {
        $this->bcStorage()->switch($id);
    }

    public function destroy(int|string $id): void
    {
        $this->bcStorage()->destroy($id);
    }

    private function bcStorage(): ContextStorage
    {
        if ($this->bc === null) {
            $this->bc = new ContextStorage();

            // Copy head into $this->bc storage to preserve already attached scopes
            /** @psalm-suppress PossiblyNullFunctionCall */
            $head = (static fn ($storage) => $storage->heads[$storage])
                ->bindTo(null, FiberBoundContextStorage::class)($this->storage);
            $head->storage = $this->bc;

            /** @psalm-suppress PossiblyNullFunctionCall */
            (static fn ($storage) => $storage->current = $storage->main = $head)
                ->bindTo(null, ContextStorage::class)($this->bc);
        }

        return $this->bc;
    }

    public function scope(): ?ContextStorageScopeInterface
    {
        return $this->bc
            ? $this->bc->scope()
            : $this->storage->scope();
    }

    public function current(): ContextInterface
    {
        return $this->bc
            ? $this->bc->current()
            : $this->storage->current();
    }

    public function attach(ContextInterface $context): ContextStorageScopeInterface
    {
        return $this->bc
            ? $this->bc->attach($context)
            : $this->storage->attach($context);
    }
}
