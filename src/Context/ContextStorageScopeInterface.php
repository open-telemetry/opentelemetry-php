<?php

declare(strict_types=1);

namespace OpenTelemetry\Context;

use ArrayAccess;

interface ContextStorageScopeInterface extends ScopeInterface, ArrayAccess
{
    public function context(): Context;

    /**
     * @param string $offset
     */
    public function offsetSet($offset, $value): void;
}
