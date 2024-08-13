<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Instrumentation;

use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\Context\Context;
use Throwable;

class Handler
{
    /**
     * @psalm-suppress ArgumentTypeCoercion
     */
    public static function pre(mixed $target, array $params, string $class, string $function, ?string $filename, ?int $lineno, ?array $span_args = [], ?array $attributes = []): void
    {
        static $instrumentation;
        $instrumentation ??= new CachedInstrumentation('io.opentelemetry.php.annotation');

        $name = $span_args['name'] ?? null;
        if ($name === null) {
            $name = empty($class)
                ? $function
                : sprintf('%s::%s', $class, $function);
        }

        $kind = $span_args['span_kind'] ?? SpanKind::KIND_INTERNAL;

        $span = $instrumentation
            ->tracer()
            ->spanBuilder($name)
            ->setSpanKind($kind)
            ->setAttribute('code.function', $function)
            ->setAttribute('code.namespace', $class)
            ->setAttribute('code.filepath', $filename)
            ->setAttribute('code.lineno', $lineno)
            ->setAttributes($attributes ?? [])
            ->startSpan();
        $context = $span->storeInContext(Context::getCurrent());
        Context::storage()->attach($context);
    }

    public static function post(mixed $target, array $params, mixed $result, ?Throwable $exception): void
    {
        $scope = Context::storage()->scope();
        $scope?->detach();

        if (!$scope || $scope->context() === Context::getCurrent()) {
            return;
        }

        $span = Span::fromContext($scope->context());
        if ($exception) {
            $span->recordException($exception, ['exception.escaped' => true]);
            $span->setStatus(StatusCode::STATUS_ERROR, $exception->getMessage());
        }

        $span->end();
    }
}
