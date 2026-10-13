[![Releases](https://img.shields.io/badge/releases-purple)](https://github.com/opentelemetry-php/sdk/releases)
[![Source](https://img.shields.io/badge/source-sdk-green)](https://github.com/open-telemetry/opentelemetry-php/tree/main/src/SDK)
[![Mirror](https://img.shields.io/badge/mirror-opentelemetry--php:sdk-blue)](https://github.com/opentelemetry-php/sdk)
[![Latest Version](http://poser.pugx.org/open-telemetry/sdk/v/unstable)](https://packagist.org/packages/open-telemetry/sdk/)
[![Stable](http://poser.pugx.org/open-telemetry/sdk/v/stable)](https://packagist.org/packages/open-telemetry/sdk/)

# OpenTelemetry SDK

The OpenTelemetry PHP SDK implements the API, and should be used in conjunction with contributed exporter(s) to generate and export telemetry.

## Documentation

https://opentelemetry.io/docs/instrumentation/php/sdk/

## Getting started

### Manual setup

See https://github.com/open-telemetry/opentelemetry-php/tree/main/examples

### SDK Builder

See https://github.com/open-telemetry/opentelemetry-php/blob/main/examples/sdk_builder.php

### Autoloading

SDK autoloading works with configuration values provided via the environment (or php.ini).

The SDK can be automatically created and registered, if the following conditions are met:
- `OTEL_PHP_AUTOLOAD_ENABLED=true`
- all required [SDK configuration](https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/sdk-environment-variables.md#general-sdk-configuration) is provided

SDK autoloading will be attempted as part of composer's autoloader:

```php
require 'vendor/autoload.php';

$tracer = \OpenTelemetry\API\Globals::tracerProvider()->getTracer('example');
$meter = \OpenTelemetry\API\Globals::meterProvider()->getMeter('example');
```

If autoloading was not successful (or partially successful), no-op implementations of the above may be returned.

See https://github.com/open-telemetry/opentelemetry-php/blob/main/examples/autoload_sdk.php for a more detailed example.

### Consistent probability samplers (experimental)

`OpenTelemetry\SDK\Trace\Sampler\Consistent` contains samplers implementing
[TraceState probability sampling](https://opentelemetry.io/docs/specs/otel/trace/tracestate-probability-sampling/):
the sampling threshold is propagated in the `ot` entry of the W3C `tracestate`, so that sampling decisions are consistent across
services and the adjusted count of sampled spans is known. Start with `ConsistentSampler`, which offers factories for
`alwaysOn()`, `alwaysOff()`, `probabilityBased()`, `parentBased()`, `ruleBased()`, `annotating()`, `anyOf()` and `rateLimited()`.

```php
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ConsistentSampler;

// 10% of root spans, children follow the parent
$sampler = ConsistentSampler::parentBased(ConsistentSampler::probabilityBased(0.1));
```

**`ConsistentSampler::rateLimited()` does not work with PHP-FPM or Apache.** The rate limiter keeps its state in the PHP process,
and every FPM/Apache request starts with an empty window, so almost all spans would be dropped instead of limited to the target rate.
Use it only in long-running processes (CLI workers, Swoole, RoadRunner, FrankenPHP worker mode, ReactPHP, ...).

The samplers `probability/development` and `composite/development` (with `always_on`, `always_off`, `probability`,
`parent_threshold` and `rule_based`) are available in declarative configuration (`open-telemetry/sdk-configuration`).
All of this is experimental and may change.

## Contributing

This repository is a read-only git subtree split.
To contribute, please see the main [OpenTelemetry PHP monorepo](https://github.com/open-telemetry/opentelemetry-php).
