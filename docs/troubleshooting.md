# Troubleshooting and FAQ

Use this guide after reading the [PHP getting started guide](https://opentelemetry.io/docs/languages/php/getting-started/).
It focuses on setup and diagnostic checks for the PHP SDK. Instrumentation packages
may have additional requirements; follow their documentation as well.

## Start with a small, local check

Before changing application code, verify the PHP process that runs the application
has the expected runtime and packages:

```shell
php -v
php --ini
php --ri opentelemetry
composer show --direct
```

`php --ri opentelemetry` reports an error when the extension is not installed or
enabled. That is expected for applications using manual instrumentation, but
zero-code instrumentation requires the extension. The [zero-code PHP guide](https://opentelemetry.io/docs/zero-code/php/)
describes the available installation approaches.

Do not include exporter headers, API keys, certificates, or other secrets when
sharing diagnostic output.

## Why do I get no spans, metrics, or logs?

For a first test, use the console exporter and disable the other signals:

```shell
OTEL_CONFIG_FILE= \
OTEL_PHP_AUTOLOAD_ENABLED=true \
OTEL_TRACES_EXPORTER=console \
OTEL_METRICS_EXPORTER=none \
OTEL_LOGS_EXPORTER=none \
php path/to/application.php
```

The application must load Composer's autoloader:

```php
require __DIR__ . '/vendor/autoload.php';
```

If SDK autoloading does not activate or only partially initializes, the API can
return no-op implementations. See the [SDK autoloading example](https://github.com/open-telemetry/opentelemetry-php/blob/main/examples/autoload_sdk.php)
and the [SDK autoloading documentation](https://github.com/open-telemetry/opentelemetry-php/blob/main/src/SDK/README.md#autoloading).

For manual instrumentation, install the API and SDK packages described by the
[PHP instrumentation documentation](https://opentelemetry.io/docs/languages/php/instrumentation/),
create a tracer or meter provider, and make sure the provider is configured with
an exporter. An instrumentation package by itself does not configure a provider
for a library.

## Why are my `OTEL_*` variables ignored?

The SDK is initialized while Composer's autoloader is being loaded. Set process
environment variables before requiring `vendor/autoload.php`, as shown in the
[autoload example](https://github.com/open-telemetry/opentelemetry-php/blob/main/examples/autoload_sdk.php):

```php
putenv('OTEL_PHP_AUTOLOAD_ENABLED=true');
putenv('OTEL_TRACES_EXPORTER=console');

require __DIR__ . '/vendor/autoload.php';
```

If a framework loads its `.env` file after Composer autoloading, those values may
arrive too late for SDK initialization. Configure the process environment or
PHP runtime before the autoloader runs. Check the CLI and web-server runtimes
separately: `php --ini` describes the CLI binary, while PHP-FPM or another SAPI
may use a different binary and `php.ini`.

To inspect only non-secret values in the current process:

```shell
php -r '
$names = [
    "OTEL_PHP_AUTOLOAD_ENABLED",
    "OTEL_TRACES_EXPORTER",
    "OTEL_EXPORTER_OTLP_PROTOCOL",
];
foreach ($names as $name) {
    printf("%s=%s\n", $name, getenv($name) ?: "<unset>");
}
'
```

The [OpenTelemetry SDK environment-variable specification](https://opentelemetry.io/docs/specs/otel/configuration/sdk-environment-variables/)
defines the standard variable names and values.

## Why does auto-instrumentation not create spans?

Check all of the following in the same PHP runtime that executes the request:

1. The OpenTelemetry PHP extension is enabled (`php --ri opentelemetry`).
2. The relevant instrumentation package is installed (`composer show --direct`).
3. `OTEL_PHP_AUTOLOAD_ENABLED=true` is available before Composer autoloading.
4. The instrumented library and its version are supported by that instrumentation package.

The [PHP zero-code documentation](https://opentelemetry.io/docs/zero-code/php/)
and the [instrumentation registry](https://opentelemetry.io/ecosystem/registry/?language=php)
are the authoritative sources for installation and package support. Start with
the console exporter before troubleshooting a remote collector.

## Why does OTLP export fail?

Confirm that the exporter package, transport, PHP extensions, protocol, and
endpoint agree. HTTP and gRPC use different transports and endpoint shapes. The
[PHP exporter documentation](https://opentelemetry.io/docs/languages/php/exporters/)
contains working examples for each protocol.

For a gRPC exporter, check that the `grpc` PHP extension and
`open-telemetry/transport-grpc` are installed. For an HTTP exporter, check that
the application has a PSR HTTP client implementation. A TCP or DNS check can
help isolate network problems, but it does not prove that the collector accepted
telemetry:

```shell
getent hosts collector.example.test
nc -vz collector.example.test 4317
nc -vz collector.example.test 4318
```

Use the host name visible from the PHP process. A host name such as `collector`
may resolve inside a container network but not from the host or another
container. For collector-side diagnosis, use the [OpenTelemetry Collector
documentation](https://opentelemetry.io/docs/collector/).

## Why does file-based configuration fail?

When `OTEL_CONFIG_FILE` is set, the SDK requires
`open-telemetry/sdk-configuration`:

```shell
composer require open-telemetry/sdk-configuration
```

YAML configuration also needs either the `symfony/yaml` Composer package or the
PHP `yaml` extension. The repository's [configuration example](https://github.com/open-telemetry/opentelemetry-php/blob/main/examples/load_config.yaml)
shows the supported file shape. Relative configuration paths are resolved from
the process working directory, so check `pwd` or use an absolute path when a
file cannot be found.

Set `OTEL_CONFIG_FILE` before loading Composer's autoloader when using SDK
autoloading. To validate a file directly, parse it explicitly and let the SDK
report syntax or component validation errors:

```php
use OpenTelemetry\Config\SDK\Configuration;

require __DIR__ . '/vendor/autoload.php';

$configuration = Configuration::parseFile('/absolute/path/to/otel-sdk.yaml');
$configuration->create();
```

See the [SDK configuration README](https://github.com/open-telemetry/opentelemetry-php/blob/main/src/Config/SDK/README.md)
for the package and file-configuration API.

## Why are spans missing from a short-lived script?

Batch processors export asynchronously. In a manually configured application,
call `shutdown()` on the tracer, meter, or logger provider after the work is
complete so queued data can be flushed. The repository's examples demonstrate
this pattern; for example, see [the batch-exporting example](https://github.com/open-telemetry/opentelemetry-php/blob/main/examples/traces/features/batch_exporting.php).

When using SDK autoloading, the SDK registers shutdown handling for its providers.
If a long-running worker or framework manages its own lifecycle, follow its
integration documentation and verify that the process remains alive long enough
for the configured processor to export.

## Where do SDK errors appear?

SDK warnings and exporter errors are written to PHP's `error_log` by default.
For a local diagnostic run, send them to stderr:

```shell
OTEL_PHP_LOG_DESTINATION=stderr php path/to/application.php
```

Supported destinations include `error_log`, `stdout`, `stderr`, `none`, `psr3`,
and `default`. The `psr3` destination requires a PSR-3 logger to be registered;
see the [logging setup example](https://github.com/open-telemetry/opentelemetry-php/blob/main/examples/troubleshooting/setting_up_logging.php).
These messages diagnose SDK initialization and export failures; they are not a
replacement for checking the collector or telemetry backend.

## What should I include when asking for help?

Include a minimal reproduction and the following non-sensitive details:

- PHP version and SAPI (`php -v` and the web-server runtime version)
- OpenTelemetry package versions (`composer show --direct`)
- whether the `opentelemetry` extension is enabled
- signal, exporter protocol, and collector host/port (omit credentials)
- whether the console-exporter check succeeds
- the relevant SDK diagnostic message and collector-side error

This makes it possible to distinguish PHP runtime, Composer, SDK configuration,
network, collector, and backend problems without exposing application secrets.
