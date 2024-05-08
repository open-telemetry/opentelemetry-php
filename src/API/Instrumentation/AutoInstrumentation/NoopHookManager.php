<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Instrumentation\AutoInstrumentation;

use Closure;
use OpenTelemetry\Context\ContextInterface;

final class NoopHookManager implements HookManager
{

    public function hook(?string $class, string $function, ?Closure $preHook = null, ?Closure $postHook = null): void
    {
        // no-op
    }

    public function enable(ContextInterface $context): ContextInterface
    {
        return $context;
    }

    public function disable(ContextInterface $context): ContextInterface
    {
        return $context;
    }
}
