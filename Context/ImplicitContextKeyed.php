<?php

declare(strict_types=1);

namespace OpenTelemetry\Context;

/**
 * Represents a value that can be sored within {@see Context}.
 * Allows storing themselves without exposing a {@see ContextKey}.
 */
interface ImplicitContextKeyed
{
    public function makeCurrent(): Scope;
    public function storeInContext(Context $context): Context;
}
