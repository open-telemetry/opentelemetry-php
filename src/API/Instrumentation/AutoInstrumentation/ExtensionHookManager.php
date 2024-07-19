<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Instrumentation\AutoInstrumentation;

use function assert;
use Closure;
use function extension_loaded;
use Nevay\SPI\ServiceProviderDependency\ExtensionDependency;
use ReflectionFunction;

/** @phan-file-suppress PhanUndeclaredClassAttribute */

#[ExtensionDependency('opentelemetry', '^1.0')]
final class ExtensionHookManager implements HookManagerInterface
{
    /**
     * @phan-suppress PhanUndeclaredFunction
     */
    public function hook(?string $class, string $function, ?Closure $preHook = null, ?Closure $postHook = null): void
    {
        assert(extension_loaded('opentelemetry'));

        /** @noinspection PhpFullyQualifiedNameUsageInspection */
        \OpenTelemetry\Instrumentation\hook($class, $function, $this->bindHookScope($preHook), $this->bindHookScope($postHook));
    }

    private function bindHookScope(?Closure $closure): ?Closure
    {
        if (!$closure) {
            return null;
        }

        $reflection = new ReflectionFunction($closure);

        // TODO Add an option flag to ext-opentelemetry `hook` that configures whether return values should be used?
        if (!$reflection->getReturnType() || (string) $reflection->getReturnType() === 'void') {
            return static function (mixed ...$args) use ($closure): void {
                if (HookManager::disabled()) {
                    return;
                }

                $closure(...$args);
            };
        }

        return static function (mixed ...$args) use ($closure): mixed {
            if (HookManager::disabled()) {
                return $args[2];
            }

            return $closure(...$args);
        };
    }
}
