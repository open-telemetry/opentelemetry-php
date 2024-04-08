<?php

declare(strict_types=1);

namespace OpenTelemetry\Context;

/**
 * @internal
 */
final class ContextStorageHead
{
    public ?ContextStorageNode $node = null;

    public function __construct(public ContextStorageHeadAware $storage)
    {
    }
}
