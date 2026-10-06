# OpenTelemetry SDK configuration

## Installation

```shell
composer require open-telemetry/sdk-configuration
```

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
ignored, so a valid file always parses.

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
```

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

## Contributing

This repository is a read-only git subtree split.
To contribute, please see the main [OpenTelemetry PHP monorepo](https://github.com/open-telemetry/opentelemetry-php).
