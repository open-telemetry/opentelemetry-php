<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Util;

use Closure;
use ReflectionFunction;
use stdClass;
use function str_starts_with;
use WeakReference;

/**
 * @internal
 */
function closure(callable $callable): Closure
{
    return Closure::fromCallable($callable);
}

/**
 * @internal
 * @see https://github.com/amphp/amp/blob/f682341c856b1f688026f787bef4f77eaa5c7970/src/functions.php#L140-L191
 */
function weaken(Closure $closure, ?object &$target = null): Closure
{
    $reflection = new ReflectionFunction($closure);
    if (!$target = $reflection->getClosureThis()) {
        return $closure;
    }

    $scope = $reflection->getClosureScopeClass();
    $name = $reflection->getShortName();
    if (!str_starts_with($name, '{closure')) {
        /** @psalm-suppress InvalidScope @phpstan-ignore-next-line @phan-suppress-next-line PhanUndeclaredThis */
        $closure = fn (...$args) => $this->$name(...$args);
        if ($scope !== null) {
            $closure = $closure->bindTo(null, $scope->name);
        }
    }

    static $placeholder;
    $placeholder ??= new stdClass();
    /** @psalm-suppress PossiblyNullReference */
    $closure = $closure->bindTo($placeholder);

    $ref = WeakReference::create($target);

    /**
     * @psalm-suppress PossiblyNullReference,PossiblyNullFunctionCall
     */
    return $scope && $target::class === $scope->name && !$scope->isInternal()
        ? static fn (...$args) => ($obj = $ref->get()) ? $closure->call($obj, ...$args) : null
        : static fn (...$args) => ($obj = $ref->get()) ? $closure->bindTo($obj)(...$args) : null;
}
