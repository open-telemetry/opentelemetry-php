# OpenTelemetry Context

Immutable execution scoped propagation mechanism, for further details see [opentelemetry-specification][1].

## Installation

```shell
composer require open-telemetry/context
```

## Usage

### Implicit propagation

```php
$context = Context::getCurrent();
// modify context
$scope = $context->activate();
try {
    // run within new context
} finally {
    $scope->detach();
}
```

It is recommended to use a `try-finally` statement after `::activate()` to ensure that the created scope is properly `::detach()`ed.

## Async applications

### Fiber support

Requires `PHP >= 8.1`, `ext-ffi` and setting the environment variable `OTEL_PHP_FIBERS_ENABLED` to a truthy value. Additionally `vendor/autoload.php` has to be preloaded for non-CLI SAPIs if [`ffi.enable`](https://www.php.net/manual/en/ffi.configuration.php#ini.ffi.enable) is set to `preload`.

### Event loops

Event loops have to restore the original context on callback execution. A basic implementation could look like the following, though implementations should avoid keeping unnecessary references to arguments if possible:

```php
function bindContext(Closure $closure): Closure {
    $context = Context::getCurrent();
    return static function (mixed ...$args) use ($closure, $context): mixed {
        $scope = $context->activate();
        try {
            return $closure(...$args);
        } finally {
            $scope->detach();
        }
    };
}
```

[1]: https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/context/README.md#context
