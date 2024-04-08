<?php

declare(strict_types=1);

namespace OpenTelemetry\Context;

/**
 * @internal
 */
interface ContextStorageHeadAware
{
    public function head(): ?ContextStorageHead;
}
