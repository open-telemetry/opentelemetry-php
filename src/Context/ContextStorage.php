<?php

declare(strict_types=1);

namespace OpenTelemetry\Context;

/**
 * @internal
 */
final class ContextStorage implements ContextStorageInterface, ContextStorageHeadAware, ExecutionContextAwareInterface
{
    private ContextStorageHead $current;
    private ContextStorageHead $main;
    /** @var array<int|string, ContextStorageHead> */
    private array $forks = [];

    public function __construct()
    {
        $this->current = $this->main = new ContextStorageHead($this);
    }

    #[\Override]
    public function fork(int|string $id): void
    {
        $this->forks[$id] = clone $this->current;
    }

    #[\Override]
    public function switch(int|string $id): void
    {
        $this->current = $this->forks[$id] ?? $this->main;
    }

    #[\Override]
    public function destroy(int|string $id): void
    {
        unset($this->forks[$id]);
    }

    #[\Override]
    public function head(): ContextStorageHead
    {
        return $this->current;
    }

    #[\Override]
    public function scope(): ?ContextStorageScopeInterface
    {
        return ($this->current->node->head ?? null) === $this->current
            ? $this->current->node
            : null;
    }

    #[\Override]
    public function current(): ContextInterface
    {
        return $this->current->node->context ?? Context::getRoot();
    }

    #[\Override]
    public function attach(ContextInterface $context): ContextStorageScopeInterface
    {
        return $this->current->node = new ContextStorageNode($context, $this->current, $this->current->node);
    }

    private function __clone()
    {
    }
}
