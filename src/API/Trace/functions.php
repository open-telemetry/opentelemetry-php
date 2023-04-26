<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace;

use Closure;
use Throwable;

/**
 * Executes the given closure within the provided span.
 *
 * The span will be ended.
 *
 * @template R
 * @param SpanInterface $span span to enclose the closure with
 * @param Closure(...): R $closure closure to invoke
 * @param iterable<int|string, mixed> $args arguments to provide to the closure
 * @return R result of the closure invocation
 *
 * @phpstan-ignore-next-line
 */
function trace(SpanInterface $span, Closure $closure, iterable $args = [])
{
    $s = $span;
    $c = $closure;
    $a = $args;
    unset($span, $closure, $args);

    $scope = $s->activate();

    try {
        /** @psalm-suppress InvalidArgument */
        return $c(...$a, ...($a = []));
    } catch (Throwable $e) {
        $s->setStatus(StatusCode::STATUS_ERROR, $e->getMessage());
        $s->recordException($e, ['exception.escaped' => true]);

        throw $e;
    } finally {
        $scope->detach();
        $s->end();
    }
}
