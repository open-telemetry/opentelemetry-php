[![Releases](https://img.shields.io/badge/releases-purple)](https://github.com/opentelemetry-php/context/releases)
[![Source](https://img.shields.io/badge/source-context-green)](https://github.com/open-telemetry/opentelemetry-php/tree/main/src/Context)
[![Mirror](https://img.shields.io/badge/mirror-opentelemetry--php:context-blue)](https://github.com/opentelemetry-php/context)
[![Latest Version](http://poser.pugx.org/open-telemetry/context/v/unstable)](https://packagist.org/packages/open-telemetry/context/)
[![Stable](http://poser.pugx.org/open-telemetry/context/v/stable)](https://packagist.org/packages/open-telemetry/context/)

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

### Debug scopes

By default, scopes created by `::activate()` warn on invalid and missing calls to `::detach()` in non-production
environments. This feature can be disabled by setting the environment variable `OTEL_PHP_DEBUG_SCOPES_DISABLED` to a
truthy value. Disabling is only recommended for applications using `exit` / `die` to prevent unavoidable notices.

## Async applications

### Fiber support - automatic context propagation to newly created fibers

Requires an NTS build, `ext-ffi`, and setting the environment variable `OTEL_PHP_FIBERS_ENABLED` to a truthy value. Additionally `vendor/autoload.php` has to be preloaded for non-CLI SAPIs if [`ffi.enable`](https://www.php.net/manual/en/ffi.configuration.php#ini.ffi.enable) is set to `preload`.

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

## Contributing

This repository is a read-only git subtree split.
To contribute, please see the main [OpenTelemetry PHP monorepo](https://github.com/open-telemetry/opentelemetry-php).

[1]: https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/context/README.md#context
