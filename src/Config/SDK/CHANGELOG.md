# Changelog

Notable changes to `open-telemetry/sdk-configuration`. Loosely follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/), with a Breaking changes section first so
upgraders can check them in one place.

This package is not 1.0 yet, so a minor release can break. Input the schema rejects becomes an
error straight away, with no deprecation step.

## Unreleased

### Breaking changes

- Configuration files must use `file_format: "1.0"`, from
  [opentelemetry-configuration v1.0.0](https://github.com/open-telemetry/opentelemetry-configuration/blob/v1.0.0/CHANGELOG.md).
  0.9.0 implemented `1.0-rc.2`. The `1.0-rc.*` and `0.x` formats are rejected. A newer `1.x`
  minor is accepted, with a warning when the SDK is created. Upstream changes that affect existing
  files include `disabled` becoming `enabled` in the tracer, meter and logger configurators, and
  the removal of `instrumentation/development.general.peer` and the `zipkin` exporter ([#2068]).
- OTLP TLS settings moved under `tls`. `certificate_file`, `client_certificate_file` and
  `client_key_file` are now `tls.ca_file`, `tls.cert_file` and `tls.key_file`, and the gRPC
  `insecure` is `tls.insecure` ([#2068]).
- The `lowmemory` temporality preference is now `low_memory` ([#2068]).
- Internal metrics are off by default. Turn them on with
  `distribution.opentelemetry_php/development.internal_metrics_enabled`. They used to be recorded
  always ([#2068]). They also need a `meter_provider` ([#2105]).
- `OTEL_CONFIG_FILE` names one file. It used to be split on commas for instrumentation config.
  `Configuration::parseFile()` takes a single path ([#2105]).
- An omitted `tracer_provider`, `meter_provider` or `logger_provider` is a noop provider, as the
  schema's `defaultBehavior` says. It used to be an SDK provider with nothing attached ([#2105]).
- A tracer or logger provider with no `processors`, or a meter provider with no `readers`, is an
  error. Remove the provider to get a noop ([#2093], [#2105]).
- `OTEL_SERVICE_NAME` is only read when the `service` resource detector is listed ([#2095]).
- A resource attribute's `type` is applied. It used to be ignored, so a value that doesn't match
  its type is now an error ([#2095]).
- Booleans from `${VAR}`, and resource attributes with `type: bool`, follow the YAML 1.2 core
  schema. `on`, `off`, `yes`, `no`, `1` and `0` are rejected. Under ext-yaml, an unquoted `on` or
  `yes` in the file is still a boolean, because the parser reads it as one ([#2105]).
- `${VAR}` is substituted as written, without trimming. A whitespace-only value no longer takes
  the `:-` default ([#2105]).
- These used to parse, and are now errors that name the config path:
  - An explicit `[]` where the schema sets `minItems: 1`, such as views, resource attributes and
    detectors, `propagator.composite`, OTLP `headers` and view `attribute_keys`. Use `~` or omit
    the key to get the default ([#2105]).
  - `''` as a resource attribute name or an OTLP header name, or an empty key in `headers_list` or
    `attributes_list` ([#2105]).
  - A view without a `selector` or `stream` ([#2068]). `null` for either, for a required component
    or list item, or for a configurator entry's `config` ([#2105]). Use `{}` for an empty object.
    A null `selector` used to match every instrument.
  - A non-string where the schema wants a string, or a list where a component map is expected.
    These used to throw a `TypeError` ([#2105]).
  - A metric reader `producers` entry that is not a map with one key, or whose properties are a
    list ([#2105]).
  - A relative or empty `ca_file`, `cert_file` or `key_file`, or `cert_file` without `key_file`
    ([#2105]).
  - `0` for a cardinality limit, or an unknown key in `cardinality_limits`. A batch processor queue
    or batch size of `0` used to fail when the SDK was built, and now fails at parse ([#2093]).
  - A multi-document YAML file under ext-yaml. symfony/yaml already rejected these ([#2105]).
  - A recursive YAML alias, or nesting deeper than 64 levels, under ext-yaml ([#2076]).
  - An `output_stream` file URI that doesn't follow RFC 8089. The host must be empty or
    `localhost` and the path absolute, so `file://x.jsonl` is rejected ([#2097]).
  - `composite` as a propagator or response propagator name, which nested a composite in
    `propagator.composite` ([#2097]).
- `instrumentation/development.general` has a fixed set of keys. It used to be a component map,
  so providers can no longer be registered under it ([#2076]).
- Component providers, the `Internal` and `Loader` namespaces and the env resource classes are
  `@internal`. `EnvResource` and `OutputStreamParser` are final ([#2097]).
- Requires PHP 8.2 ([#2091]), `open-telemetry/api` 1.11 and `open-telemetry/sdk` 1.16 ([#2076]),
  `composer-runtime-api` 2 and `psr/log` ([#2097]).

### Added

- Composable samplers: `composite/development` and `probability/development`, with `always_on`,
  `always_off`, `parent_threshold`, `probability` and `rule_based` composables. All are
  experimental ([#2077]).
- `log_level` sets the level of the SDK's internal logger. An omitted value means `info`, and
  `OTEL_LOG_LEVEL` is not read. It applies to messages logged while the SDK is created, and after
  `SdkBuilder::buildAndRegisterGlobal()` ([#2068]).
- `compression: none` for OTLP exporters ([#2068]).
- View `selector.instrument_type: gauge` ([#2068]).
- The console metric exporter takes `temporality_preference`. `default_histogram_aggregation`
  parses but is not applied yet ([#2068]).
- Logger configurator `minimum_severity` and `trace_based` parse, but are not applied yet
  ([#2068]).
- View aggregations: `drop`, `sum`, `last_value` and `explicit_bucket_histogram` with
  `record_min_max` ([#2093]). Histogram `boundaries` must be strictly ascending ([#2095]).
  `base2_exponential_bucket_histogram` parses, then warns and uses the reader's default ([#2093]).
- `*` and `?` patterns in view `attribute_keys.included` and `excluded`. Excluded wins ([#2095]).
- `composite_list` accepts `none`, as `OTEL_PROPAGATORS` does ([#2097]).
- `Configuration::getInstrumentationConfig()`, which reads instrumentation config from the same
  file as the SDK, and `ConfigProvider`, which serves it through SPI. Both are experimental
  ([#2076]).
- `IgnoresUnknownProviders`, for a component map that skips unknown keys ([#2068]).
- `GetenvEnvSource` ([#2095]).
- `Validation::ensureKeyValueList()` and `Validation::ensureAbsolutePath()` ([#2105]).

### Changed

- Unknown resource detectors and `distribution` keys are skipped with a warning, not rejected
  ([#2068]). So are unknown `instrumentation/development.php` entries ([#2076]).
- Quoted values on boolean, integer and float settings are converted, so `"true"` and `"5"` work
  ([#2105]).
- A trailing or doubled comma in `headers_list` or `attributes_list` is skipped ([#2105]).
- `max_export_batch_size` above `max_queue_size` is lowered to the queue size with a warning. It
  used to throw ([#2105]).
- A view with `stream.name` and a missing or wildcard `instrument_name` warns ([#2105]).
- The batch log processor `schedule_delay` defaults to 1000, not 5000 ([#2095]).
- Span event and link attribute count limits default to 128, and no longer inherit
  `attribute_count_limit` ([#2095]).
- The periodic metric reader `interval` defaults to 60000, not 5000. The reader doesn't use it
  yet ([#2105]).
- A view's `aggregation_cardinality_limit` defaults to null, not 2000. Cardinality limits are not
  applied yet ([#2093]).
- The cache file and its metadata are written with mode 0600. A cache that can't be read is a
  miss, and one that can't be written logs a warning. Both used to throw ([#2097]).
- `Validation::ensureNumber()` returns a number, not the string it was given ([#2097]).
- A file without a `.yaml` or `.yml` extension throws `InvalidArgumentException`, not symfony's
  `LoaderLoadException` ([#2105]).

### Deprecated

These are to be removed before 1.0.

- The top-level `response_propagator/development` key. Move it under
  `distribution.opentelemetry_php/development` ([#2093]).
- Registering providers under the `OpenTelemetry\Config\SDK\Configuration\ComponentProvider` SPI
  key, which now logs a warning. Use `OpenTelemetry\API\Configuration\Config\ComponentProvider`
  ([#2097]).
- The alias classes in `OpenTelemetry\Config\SDK\Configuration`. Use their equivalents in
  `OpenTelemetry\API\Configuration\Config` and `OpenTelemetry\API\Configuration\Context`
  ([#2097]).
- `OpenTelemetry\Config\SDK\Instrumentation`. Use `Configuration::parseFile()` and
  `getInstrumentationConfig()` ([#2076]).

### Removed

- `SpanExporterZipkin` and `PeerConfigProvider`, with their schema nodes ([#2068]).
- `TextMapPropagatorComposite` and `ResponsePropagatorComposite` ([#2097]).
- `Validation::ensureRegexPattern()` ([#2097]).

### Fixed

- `temporality_preference` is applied per instrument kind. `delta` made up-down counters delta,
  and `lowmemory` did the same to synchronous up-down counters ([#2095]).
- The OTLP client key was read from `client_certificate_file` ([#2068]).
- gRPC exporters accepted `insecure` and ignored it. `tls.insecure` is applied ([#2068]).
- A view's `selector.unit` was ignored ([#2093]).
- A view's `stream.aggregation` was ignored. Only `default` was available ([#2093]).
- A view's `attribute_keys.excluded` was ignored ([#2095]).
- Hex integers such as `0xdeadbeef` from `${VAR}` are accepted on integer settings ([#2093]).
- A configured resource `schema_url` is kept after detectors are merged ([#2095]).
- A numeric resource attribute name such as `123` keeps its name ([#2105]).
- `${VAR}` reads the same environment sources that locate `OTEL_CONFIG_FILE` ([#2095]).
- A null object node, such as `resource:`, takes the schema defaults. An omitted sampler `ratio`
  or `root` takes its default ([#2095]).
- Under ext-yaml, an escaped `$${VAR}` reached through a YAML alias stays literal ([#2076]).

Some fixes to settings read through declarative config ship in other packages. See their release
notes:

- `open-telemetry/sdk`: an unparseable `OTEL_CONFIG_FILE` logs a warning and gives a noop SDK,
  where it used to end the process. Also temporality selection, and TLS options with Symfony
  HttpClient.
- `open-telemetry/exporter-otlp`: header handling, and a gRPC endpoint with a trailing `/`.
- `open-telemetry/transport-grpc`: `timeout: 0` means no limit.

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
