<?php

declare(strict_types=1);

namespace OpenTelemetry\Context;

interface ContextStorageInterface
{
    public function scope(): ?ContextStorageScopeInterface;

    public function current(): ContextInterface;

    public function attach(ContextInterface $context): ContextStorageScopeInterface;
}
