# Long-lived PHP workers

Traditional PHP applications start a process for a request and run shutdown
handlers at the end of that request. Long-lived worker runtimes keep the PHP
process alive for multiple units of work. This includes FrankenPHP worker mode,
RoadRunner, queue workers, and custom daemon processes.

OpenTelemetry state must therefore be completed explicitly at the end of every
unit of work. Do not rely on PHP process shutdown to finish a request span or
export its telemetry.

## Per-request lifecycle

For each request or job, establish a fresh root context before application work
begins. Start and activate the request span inside that context, then end the
span and detach both scopes before accepting another unit of work.

```php
use OpenTelemetry\Context\Context;

$rootScope = Context::getRoot()->activate();

try {
    $span = $tracer->spanBuilder('worker.request')->startSpan();
    $spanScope = $span->activate();

    try {
        // Handle exactly one request or job.
    } finally {
        $span->end();
        $spanScope->detach();
    }
} finally {
    $rootScope->detach();
}
```

Using `Context::getRoot()` prevents telemetry from a previous unit of work from
becoming the parent of the next one. If your application deliberately keeps a
trace across units of work, use the context propagation rules appropriate for
that application instead.

## Flush after each unit of work

After completing the request or job, flush the providers owned by the
application. The SDK exposes `forceFlush()` on the tracer, meter, and logger
providers. Flush every signal provider that the application configured.

```php
$tracerProvider->forceFlush();
$meterProvider->forceFlush();
$loggerProvider->forceFlush();
```

`forceFlush()` requests export of pending data; it does not replace the normal
process-level shutdown. Call `shutdown()` when the worker itself is stopping,
not after every request, because shutdown makes a provider unavailable for
future work.

## Configuration timing

Automatic instrumentation and SDK configuration can be initialized before a
framework loads its `.env` files. A `.env` file can provide `OTEL_*`
configuration when its loader runs before OpenTelemetry initialization; whether
that is true depends on the installed packages and their initialization order.
For predictable worker startup, set `OTEL_*` configuration in the worker
process environment (for example, the container or process-manager
configuration) before Composer autoloading starts. A framework-specific `.env`
file may be visible to application code yet still be too late for OpenTelemetry
initialization.

## Troubleshooting checklist

When telemetry is missing, delayed, or repeated between requests:

1. Confirm the `OTEL_*` variables exist in the worker's real process
   environment at startup.
2. Confirm every request or job starts from a fresh root context unless a
   shared trace is intentional.
3. Confirm request spans are ended and all attached scopes are detached.
4. Call `forceFlush()` for each configured signal provider after the unit of
   work completes.
5. Call provider `shutdown()` only as part of worker termination.

This lifecycle is independent of web-server tracing such as Caddy's tracing:
server telemetry and PHP application telemetry are separate instrumentation
concerns.
