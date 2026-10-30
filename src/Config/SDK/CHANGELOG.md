# Changelog

Notable changes to `open-telemetry/sdk-configuration`. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/). Breaking changes are listed first, so
upgraders can check them in one place.

This package is not 1.0 yet, so a minor release can break. Input the schema rejects becomes an
error straight away, with no deprecation step.

## Unreleased

### Breaking changes

- Configuration files must use `file_format: "1.0"`, from
  [opentelemetry-configuration v1.0.0](https://github.com/open-telemetry/opentelemetry-configuration/blob/v1.0.0/CHANGELOG.md).
  0.9.0 implemented `1.0-rc.2`. The `1.0-rc.*` and `0.x` formats are rejected. A newer `1.x`
  minor is accepted with a warning. Upstream changes that affect existing files include `disabled`
  becoming `enabled` in the tracer, meter and logger configurators, and the removal of
  `instrumentation/development.general.peer`, the `ottrace` propagator and the `zipkin` exporter
  ([#2068]).
- `OTEL_CONFIG_FILE` names one file. It used to be split on commas and the files merged.
  `Configuration::parseFile()` takes a single path ([#2105]).
- An omitted `tracer_provider`, `meter_provider` or `logger_provider` is a noop provider, as the
  schema's `defaultBehavior` says. It used to be an SDK provider with nothing attached. Internal
  metrics are only recorded when a `meter_provider` is configured ([#2105]).
- A tracer or logger provider with no `processors`, or a meter provider with no `readers`, is an
  error. Remove the provider to get a noop ([#2093]).
- `0` is rejected where the schema sets `exclusiveMinimum: 0`: batch processor queue and batch
  sizes, and cardinality limits ([#2093]).
- Values the schema rejects are errors that name the config path ([#2105]):
  - An explicit `[]` where the schema sets `minItems: 1`, such as views, resource attributes and
    detectors, `propagator.composite`, OTLP `headers` and view `attribute_keys`. Use `~` or omit
    the key to get the default.
  - `''` as a resource attribute name, a rule-based sampler attribute key or an OTLP header name.
  - `null` for a required component or list item, a view's `selector` or `stream`, or a
    configurator entry's `config`. Use `{}` for an empty object. A null `selector` used to match
    every instrument.
  - A non-string where the schema wants a string, or a list where a component map is expected.
    These used to throw a `TypeError`, or were skipped silently.
  - A metric reader `producers` entry that is not a map with one key, or whose properties are a
    list.
  - A relative or empty `ca_file`, `cert_file` or `key_file`, or `cert_file` without `key_file`.
  - A multi-document YAML file under ext-yaml. symfony/yaml already rejected these.
  - A file whose name doesn't end in `.yaml` or `.yml`.
- Booleans follow the YAML 1.2 core schema. `on`, `off`, `yes`, `no`, `1` and `0` from `${VAR}`,
  or as a `type: bool` resource attribute, are rejected. ext-yaml still reads an unquoted `on` or
  `yes` in the file as a boolean ([#2105]).
- `${VAR}` is substituted as written. A whitespace-only value no longer takes the `:-` default
  ([#2105]).
- Histogram `boundaries` must be a strictly ascending list ([#2095]).
- An `output_stream` file URI follows RFC 8089. The host must be empty or `localhost` and the path
  absolute, so `file://x.jsonl` is rejected ([#2097]).
- `composite` is no longer a propagator or response propagator name, so it can't be nested in
  `propagator.composite` ([#2097]).
- Most component providers and the `Internal` and `Loader` namespaces are `@internal`.
  `DistributionConfiguration` and `DistributionProperties` are `@experimental` ([#2097]).
- Requires PHP 8.2 ([#2091]), `open-telemetry/api` 1.11 and `open-telemetry/sdk` 1.16.

### Added

- Composable samplers: `composite/development` and `probability/development`, with `always_on`,
  `always_off`, `parent_threshold`, `probability` and `rule_based` composables. All are
  experimental ([#2077]).
- The `log_level` setting for the SDK's internal logger ([#2068]).
- View aggregations: `drop`, `sum`, `last_value` and `explicit_bucket_histogram` with
  `record_min_max`. `base2_exponential_bucket_histogram` parses, then warns and uses the reader's
  default ([#2093]).
- View `attribute_keys.excluded`, and `*` and `?` patterns in `included` and `excluded`. Excluded
  wins ([#2095]).
- `composite_list` accepts `none`, as `OTEL_PROPAGATORS` does ([#2097]).
- `Configuration::getInstrumentationConfig()`, which reads instrumentation config from the same
  parse as the SDK ([#2076]).

### Changed

- Unknown resource detectors and `distribution` keys are skipped with a warning, not rejected
  ([#2068]). So are unknown `instrumentation/development.php` entries ([#2076]).
- Quoted values on boolean, integer and float settings are converted, so `"true"` and `"5"` work
  ([#2105]).
- A trailing or doubled comma in `headers_list` or `attributes_list` is skipped. An empty key is an
  error ([#2105]).
- `max_export_batch_size` above `max_queue_size` is lowered to the queue size with a warning. It
  used to throw ([#2105]).
- A view with `stream.name` and a missing or wildcard `instrument_name` warns ([#2105]).
- A newer `file_format` minor warns from `create()`, through the configured `log_level` ([#2105]).
- The periodic metric reader `interval` defaults to 60000. The reader doesn't use it yet
  ([#2105]).
- A view's `aggregation_cardinality_limit` defaults to null, not 2000. Cardinality limits are not
  applied yet ([#2093]).
- The cache file and its metadata are written with mode 0600. A cache that can't be written or
  read logs a warning and is treated as a miss ([#2097]).
- A missing or unparseable `OTEL_CONFIG_FILE` logs an error and gives a noop SDK. It used to end
  the process with exit code 255 ([#2076]).

### Deprecated

These are to be removed before 1.0.

- The top-level `response_propagator/development` key. Move it under
  `distribution.opentelemetry_php/development` ([#2093]).
- Registering providers under the `OpenTelemetry\Config\SDK\Configuration\ComponentProvider` SPI
  key, and the alias classes in `OpenTelemetry\Config\SDK\Configuration`. Use the
  `OpenTelemetry\API\Configuration\Config` equivalents ([#2097]).
- `OpenTelemetry\Config\SDK\Instrumentation`. Use `Configuration::parseFile()` and
  `getInstrumentationConfig()` ([#2076]).

### Fixed

- `temporality_preference` is applied per instrument kind. `delta` made up-down counters delta,
  and `low_memory` did the same to synchronous up-down counters ([#2095]).
- A view's `selector.unit` was ignored ([#2093]).
- A view's `stream.aggregation` was ignored ([#2093]).
- `tls.insecure` is honoured for gRPC exporters ([#2068]).
- A gRPC endpoint with a trailing `/` is accepted ([#2095]).
- Hex integers such as `0xdeadbeef` from `${VAR}` are accepted on integer settings ([#2093]).
- Resource attribute `type` is applied after substitution ([#2095]).
- A configured resource `schema_url` is kept after detectors are merged ([#2095]).
- A numeric resource attribute name such as `123` keeps its name ([#2105]).
- `OTEL_SERVICE_NAME` is not read when a config file is in use, unless the `service` detector is
  listed ([#2095]).
- `${VAR}` reads the same environment sources that locate `OTEL_CONFIG_FILE` ([#2095]).
- A null object node, such as `resource:`, takes the schema defaults. An omitted sampler `ratio`
  or `root` takes its default ([#2095]).
- The batch log processor `schedule_delay` defaults to 1000, and span event and link attribute
  count limits default to 128 ([#2095]).
- Batch processor self-observability follows `internal_metrics_enabled` ([#2095]).
- An invalid SDK section no longer discards instrumentation config read through `ConfigProvider`
  ([#2095]).
- A `file_format: 1.0` without quotes gives an error that says to quote it ([#2105]).

Some fixes to settings read through declarative config ship in `open-telemetry/sdk` and
`open-telemetry/exporter-otlp`, such as `timeout: 0` meaning no limit, header handling and TLS
options with Symfony HttpClient. See their release notes.

## 0.9.0 and earlier

See the [GitHub releases](https://github.com/opentelemetry-php/config-sdk/releases).

[#2068]: https://github.com/open-telemetry/opentelemetry-php/pull/2068
[#2076]: https://github.com/open-telemetry/opentelemetry-php/pull/2076
[#2077]: https://github.com/open-telemetry/opentelemetry-php/pull/2077
[#2091]: https://github.com/open-telemetry/opentelemetry-php/pull/2091
[#2093]: https://github.com/open-telemetry/opentelemetry-php/pull/2093
[#2095]: https://github.com/open-telemetry/opentelemetry-php/pull/2095
[#2097]: https://github.com/open-telemetry/opentelemetry-php/pull/2097
[#2105]: https://github.com/open-telemetry/opentelemetry-php/pull/2105
