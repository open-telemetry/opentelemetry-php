<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Instrumentation\AutoInstrumentation;

use Closure;

final class NoopHookManager implements HookManager
{
    use EnableTrait;

    public function hook(?string $class, string $function, ?Closure $preHook = null, ?Closure $postHook = null): void
    {
        // no-op
    }
}
