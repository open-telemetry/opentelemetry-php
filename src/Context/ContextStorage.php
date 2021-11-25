<?php

declare(strict_types=1);

namespace OpenTelemetry\Context;

/**
 * @internal
 */
final class ContextStorage implements ContextStorageInterface
{
    public ContextStorageHead $current;
    private ContextStorageHead $main;
    /** @var array<int, ContextStorageHead> */
    private array $forks = [];

    public function __construct(Context $context)
    {
        $this->current = $this->main = new ContextStorageHead($this);
        $this->current->node = new ContextStorageNode($context, $this->current);
    }

    public function fork(int $id): void
    {
        $this->forks[$id] = clone $this->current;
    }

    public function switch(int $id): void
    {
        $this->current = $this->forks[$id] ?? $this->main;
    }

    public function destroy(int $id): void
    {
        unset($this->forks[$id]);
    }

    public function current(): Context
    {
        return $this->current->node->context;
    }

    public function attach(Context $context): ScopeInterface
    {
        return $this->current->node = new ContextStorageNode($context, $this->current, $this->current->node);
    }

    private function __clone()
    {
    }
}
