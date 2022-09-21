<?php

declare(strict_types=1);

namespace OpenTelemetry\Context;

use ArrayAccess;

interface ContextStorageScopeInterface extends ScopeInterface, ArrayAccess
{
    /**
     * Returns the context associated with this scope.
     *
     * @return ContextInterface associated context
     */
    public function context(): ContextInterface;

    /**
     * @param string $offset
     */
    public function offsetSet($offset, $value): void;
}
