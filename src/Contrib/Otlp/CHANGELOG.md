# Changelog

Notable changes to `open-telemetry/exporter-otlp`. Loosely follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/), with a Breaking changes section first so
upgraders can check them in one place.

## Unreleased

### Breaking changes

- Requires `open-telemetry/sdk` 1.17 and `tbachert/spi`. The exporter and transport factories are registered through
  SPI. Allow the `tbachert/spi` Composer plugin for the early registration, without it they are registered when the
  package's autoload file loads, as before ([#2120]).

### Deprecated

- Passing a `Temporality` or a string as the temporality of `MetricExporter`. Pass a selector such as
  `TemporalityPreference::cumulative()` or `TemporalityPreference::delta()`. The delta preference keeps
  UpDownCounters cumulative, as the specification requires, where `Temporality::DELTA` made them delta. An exporter
  built without a temporality is not deprecated. It uses each stream's temporality until 2.0, and cumulative from
  2.0. Pass `TemporalityPreference::cumulative()` to get that now ([#2129]).

### Fixed

- With the `tbachert/spi` plugin running, the `otlp` exporters and the `http` transport are available while the SDK
  autoloads, whatever the order in which Composer includes the packages ([#2120]).

## 1.5.0 - 2026-11-06

### Breaking changes

- Requires PHP 8.2 ([#2091]) and `open-telemetry/sdk` 1.16 ([#2095]).
- Requires `open-telemetry/gen-otlp-protobuf` 1.11.1, which requires `google/protobuf` 4.31.
  `ext-protobuf`, if installed, must also be 4.31. The exporters throw a `RuntimeException` on
  construction if it is older. JSON export encodes enums with
  `PrintOptions::ALWAYS_PRINT_ENUMS_AS_INTS`, and the fallback for older versions is removed
  ([#2110]).

### Added

- `OTEL_EXPORTER_OTLP_[SIGNAL_]CERTIFICATE`, `OTEL_EXPORTER_OTLP_[SIGNAL_]CLIENT_CERTIFICATE` and
  `OTEL_EXPORTER_OTLP_[SIGNAL_]CLIENT_KEY` are passed to the transport ([#2059]).
- `OTEL_EXPORTER_OTLP_[SIGNAL_]INSECURE`. A gRPC endpoint without a scheme uses `https`, or
  `http` when this is true ([#2059]).
- `MetricExporter` accepts an `AggregationTemporalitySelectorInterface` ([#2095]).

### Changed

- The `User-Agent` header is `OTel-OTLP-Exporter-PHP/<version>`, as the spec says ([#2092]).
- Histogram data points without min and max, such as from `record_min_max: false`, are exported
  without them ([#2093]).

### Deprecated

- A gRPC endpoint with a path. It is used as is, with a deprecation warning, and will become an
  error ([#2059]).

### Fixed

- With `grpc` and no endpoint set, the exporter used the HTTP default `http://localhost:4318`. It
  uses `http://localhost:4317` ([#2059]).
- A signal-specific gRPC endpoint, such as `OTEL_EXPORTER_OTLP_TRACES_ENDPOINT`, was used without
  the gRPC method ([#2059]).
- A trailing `/` on a gRPC endpoint was read as a path ([#2095]).
- `OTEL_EXPORTER_OTLP_METRICS_TEMPORALITY_PREFERENCE` is applied per instrument kind. `delta` made
  up-down counters delta too ([#2095]).
- A configured `User-Agent` was sent beside the default one when its case differed. Header names
  merge case-insensitively, and a configured header wins ([#2095]).
- Declarative config `headers_list` values are percent-decoded, as `OTEL_EXPORTER_OTLP_HEADERS`
  values are ([#2095]).

## 1.4.0 and earlier

See the [GitHub releases](https://github.com/opentelemetry-php/exporter-otlp/releases).

[#2059]: https://github.com/open-telemetry/opentelemetry-php/pull/2059
[#2091]: https://github.com/open-telemetry/opentelemetry-php/pull/2091
[#2092]: https://github.com/open-telemetry/opentelemetry-php/pull/2092
[#2093]: https://github.com/open-telemetry/opentelemetry-php/pull/2093
[#2095]: https://github.com/open-telemetry/opentelemetry-php/pull/2095
[#2110]: https://github.com/open-telemetry/opentelemetry-php/pull/2110
[#2120]: https://github.com/open-telemetry/opentelemetry-php/pull/2120
[#2129]: https://github.com/open-telemetry/opentelemetry-php/pull/2129
