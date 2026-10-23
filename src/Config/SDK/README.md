# OpenTelemetry SDK configuration

## Installation

```shell
composer require open-telemetry/sdk-configuration
```

### Choose a YAML parser

Loading a YAML file needs `symfony/yaml` or ext-yaml. Prefer `symfony/yaml`:

```shell
composer require symfony/yaml
```

The spec says files SHOULD be parsed with the
[YAML 1.2 core schema](https://github.com/open-telemetry/opentelemetry-specification/blob/v1.61.0/specification/configuration/data-model.md#yaml-file-format).
`symfony/yaml` follows it for booleans. ext-yaml parses YAML 1.1, where `yes`, `no`, `on` and `off`
are booleans. With ext-yaml, quote those values when you mean a string.

When both are installed, `symfony/yaml` is used.

## Usage

### Initialization from [configuration file](https://opentelemetry.io/docs/specs/otel/configuration/file-configuration/)

```php
$configuration = Configuration::parseFile(__DIR__ . '/otel-sdk-config.yaml');
$sdkBuilder = $configuration->create();
```

#### Supported file format

This package's major version matches the `file_format` major version it implements: `1.x` of this
package implements `file_format: "1.x"`, as defined by
[opentelemetry-configuration](https://github.com/open-telemetry/opentelemetry-configuration/tree/v1.0.0).
Only one schema major version is supported at a time.

Within that major version, the
[file format rules](https://github.com/open-telemetry/opentelemetry-configuration/blob/v1.0.0/VERSIONING.md#file-format)
apply:

- a `MINOR` version at or below the implemented one is used as-is;
- a higher `MINOR` version is accepted with a warning, since minor schema changes are additive, but
  properties it introduces may not be acted on yet;
- a different `MAJOR` version is an error, as it may reinterpret existing properties.

The `1.0-rc.*` release candidates and the superseded `0.x` formats are rejected.

Quote the version: an unquoted `1.0` is YAML for a float, not the string `"1.0"`.

Not every property of the schema is acted on yet; unimplemented ones are validated and
ignored, so a valid file always parses. See
[Settings that parse but have no effect](#settings-that-parse-but-have-no-effect).

#### Distribution-specific settings

The `distribution` key is the schema's standardized location for settings outside the OpenTelemetry
configuration model. Each key under it names a distribution; this one uses
`opentelemetry_php/development`:

```yaml
file_format: "1.0"
distribution:
  opentelemetry_php/development:
    span_suppression_strategy/development:
      semconv:         # or `spankind:`; omit the block for no suppression
    response_propagator/development:
      composite_list: servertiming,traceresponse
```

`response_propagator/development` takes `composite` and `composite_list`, like the schema's
`propagator`. Response propagators aren't in the specification. The top-level
`response_propagator/development` key is deprecated. It still works and logs a warning, and the
`distribution` one wins when both are set.

Any distribution may claim a key there, so one file can carry the settings of several. Keys this SDK
has no provider for — another distribution's, or one whose package is not installed — are ignored
and logged at warning level, rather than failing the parse. Note that this makes a typo in the key
above silently fall back to the default instead of raising an error; the warning is where it shows
up. It is emitted when the SDK is created, so a configured `log_level` applies to it.

#### Performance considerations

Parsing and processing the configuration is rather expensive. It is highly recommended to provide the `$cacheFile`
parameter when running in a shared-nothing setup.

```php
$configuration = Configuration::parseFile(
    __DIR__ . '/otel-sdk-config.yaml',
    __DIR__ . '/var/cache/opentelemetry.php',
);
$sdkBuilder = $configuration->create();
```

## Deviations from the specification

### Settings that parse but have no effect

These are validated, so a file that sets them still parses, but the SDK doesn't act on them yet:

- `logger_provider.limits`. Log record limits come from the `OTEL_LOGRECORD_*` and
  `OTEL_ATTRIBUTE_*` environment variables only.
- `meter_provider.exemplar_filter`.
- A view's `stream.attribute_keys.excluded`. `included` works.
- A view's `stream.aggregation_cardinality_limit`, and a reader's `cardinality_limits`. The metrics
  SDK has no cardinality limits.
- A reader's `producers`.
- The periodic reader's `interval` and `timeout`.
- `default_histogram_aggregation` on the metric exporters.
- `minimum_severity` and `trace_based` in `logger_configurator/development`.

### View aggregations

- `base2_exponential_bucket_histogram` is validated, then logs a warning and uses the reader's
  default aggregation. The SDK has no exponential histogram.
- `explicit_bucket_histogram` without `boundaries` takes them from the instrument's advisory
  parameters. Without those, it uses the SDK's default of
  `[0, 5, 10, 25, 50, 75, 100, 250, 500, 1000]`, not the 15 boundaries in the schema. The spec
  [allows the older set](https://github.com/open-telemetry/opentelemetry-specification/blob/v1.61.0/specification/metrics/sdk.md#explicit-bucket-histogram-aggregation)
  for backwards compatibility.
- A view whose aggregation doesn't suit the instrument, such as `explicit_bucket_histogram` on an
  asynchronous instrument, is applied as given. The spec says it
  [SHOULD warn and ignore the view](https://github.com/open-telemetry/opentelemetry-specification/blob/v1.61.0/specification/metrics/sdk.md#measurement-processing).

### Instrumentation configuration

`Configuration::getInstrumentationConfig()` returns a `ConfigProperties`. In the
[spec](https://github.com/open-telemetry/opentelemetry-specification/blob/v1.61.0/specification/configuration/api.md#configproperties),
`ConfigProperties` is a view of one mapping node, with typed accessors for its scalars, mappings
and sequences. Here it is a registry of instrumentation configuration objects keyed by class:

```php
$http = $configuration->getInstrumentationConfig()->get(HttpConfig::class);
```

`ConfigProperties` is marked experimental. The spec's accessors will be added alongside `get()`,
which stays.

## Contributing

This repository is a read-only git subtree split.
To contribute, please see the main [OpenTelemetry PHP monorepo](https://github.com/open-telemetry/opentelemetry-php).
