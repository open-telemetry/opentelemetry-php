<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Instrumentation\AutoInstrumentation;

use function assert;
use Closure;
use function extension_loaded;
use Nevay\SPI\ServiceProviderDependency\ExtensionDependency;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextKeyInterface;
use ReflectionFunction;

#[ExtensionDependency('opentelemetry', '^1.0')]
final class ExtensionHookManager implements HookManager
{

    private readonly ContextKeyInterface $contextKey;

    public function __construct()
    {
        $this->contextKey = Context::createKey(self::class);
    }

    /**
     * @phan-suppress PhanUndeclaredFunction
     */
    public function hook(?string $class, string $function, ?Closure $preHook = null, ?Closure $postHook = null): void
    {
        assert(extension_loaded('opentelemetry'));

        /** @noinspection PhpFullyQualifiedNameUsageInspection */
        \OpenTelemetry\Instrumentation\hook($class, $function, $this->bindHookScope($preHook), $this->bindHookScope($postHook));
    }

    public function enable(Context $context): Context
    {
        return $context->with($this->contextKey, true);
    }

    public function disable(Context $context): Context
    {
        return $context->with($this->contextKey, null);
    }

    private function bindHookScope(?Closure $closure): ?Closure
    {
        if (!$closure) {
            return null;
        }

        $contextKey = $this->contextKey;
        $reflection = new ReflectionFunction($closure);

        // TODO Add an option flag to ext-opentelemetry `hook` that configures whether return values should be used?
        if (!$reflection->getReturnType() || (string) $reflection->getReturnType() === 'void') {
            return static function (mixed ...$args) use ($closure, $contextKey): void {
                /*if (!Context::getCurrent()->get($contextKey)) {
                    return;
                }*/

                $closure(...$args);
            };
        }

        return static function (mixed ...$args) use ($closure, $contextKey): mixed {
            /*if (!Context::getCurrent()->get($contextKey)) {
                return null;
            }*/

            return $closure(...$args);
        };
    }
}
