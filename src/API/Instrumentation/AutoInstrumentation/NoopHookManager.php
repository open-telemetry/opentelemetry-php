<?php declare(strict_types=1);
namespace OpenTelemetry\API\Instrumentation\AutoInstrumentation;

use Closure;
use OpenTelemetry\Context\Context;

final class NoopHookManager implements HookManager {

    public function hook(?string $class, string $function, ?Closure $preHook = null, ?Closure $postHook = null): void {
        // no-op
    }

    public function enable(Context $context): Context {
        return $context;
    }

    public function disable(Context $context): Context {
        return $context;
    }
}
