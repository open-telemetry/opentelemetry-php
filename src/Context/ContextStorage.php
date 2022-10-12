<?php

declare(strict_types=1);

namespace OpenTelemetry\Context;

/**
 * @internal
 */
final class ContextStorage implements ContextStorageInterface, ExecutionContextAwareInterface
{
    public ContextStorageHead $current;
    private ContextStorageHead $main;
    /** @var array<int|string, ContextStorageHead> */
    private array $forks = [];

    public function __construct()
    {
        $this->current = $this->main = new ContextStorageHead($this);
    }

    public function fork($id): void
    {
        $this->forks[$id] = clone $this->current;
    }

    public function switch($id): void
    {
        $this->current = $this->forks[$id] ?? $this->main;
    }

    public function destroy($id): void
    {
        unset($this->forks[$id]);
    }

    public function scope(): ?ContextStorageScopeInterface
    {
        return ($this->current->node->head ?? null) === $this->current
            ? $this->current->node
            : null;
    }

    public function current(): ContextInterface
    {
        return $this->current->node->context ?? Context::getRoot();
    }

    public function attach(ContextInterface $context): ContextStorageScopeInterface
    {
        return $this->current->node = new ContextStorageNode($context, $this->current, $this->current->node);
    }

    private function __clone()
    {
    }
}
