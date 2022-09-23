<?php

declare(strict_types=1);

namespace OpenTelemetry\Context;

interface ContextStorageInterface
{
    /**
     * Returns the current scope.
     *
     * @return ContextStorageScopeInterface|null current scope, or null if no
     *         scope was attached in the current execution unit
     */
    public function scope(): ?ContextStorageScopeInterface;

    /**
     * Returns the current context.
     *
     * @return ContextInterface current context
     */
    public function current(): ContextInterface;

    /**
     * Attaches the context as active context.
     *
     * @param ContextInterface $context context to attach
     * @return ContextStorageScopeInterface scope to detach the context and
     *         restore the previous context
     */
    public function attach(ContextInterface $context): ContextStorageScopeInterface;
}
