<?php

declare(strict_types=1);

namespace OpenTelemetry\Context\ScopeBound;

use OpenTelemetry\Context\Context;

/**
 * @internal
 */
final class ContextHolder
{
    public Context $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }
}
