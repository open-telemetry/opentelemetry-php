# OpenTelemetry SDK

The OpenTelemetry PHP SDK implements the API, and should be used in conjunction with contributed exporter(s) to generate and export telemetry.

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

$tracer = OpenTelemetry\API\Common\Instrumentation\Globals::tracerProvider()->getTracer('example');
$meter = OpenTelemetry\API\Common\Instrumentation\Globals::meterProvider()->getMeter('example');
```

If autoloading was not successful (or partially successful), no-op implementations of the above may be returned.

See https://github.com/open-telemetry/opentelemetry-php/blob/main/examples/autoload_sdk.php for a more detailed example.
