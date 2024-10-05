<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Instrumentation\AutoInstrumentation;

use Closure;
use Throwable;

interface HookManagerInterface
{
    /**
     * @param ?Closure(object|string|null, array, ?string, string, ?string, ?int): (array|null|void) $preHook
     * @param ?Closure(object|string|null, array, mixed, ?Throwable): (mixed|void) $postHook
     */
    public function hook(?string $class, string $function, ?Closure $preHook = null, ?Closure $postHook = null): void;
}
