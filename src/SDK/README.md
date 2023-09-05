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

## Contributing

This repository is a read-only git subtree split.
To contribute, please see the main [OpenTelemetry PHP monorepo](https://github.com/open-telemetry/opentelemetry-php).
