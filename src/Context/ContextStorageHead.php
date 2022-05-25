<?php

declare(strict_types=1);

namespace OpenTelemetry\Context;

/**
 * @internal
 */
final class ContextStorageHead
{
    public ContextStorage $storage;
    public ?ContextStorageNode $node = null;

    public function __construct(ContextStorage $storage)
    {
        $this->storage = $storage;
    }
}
