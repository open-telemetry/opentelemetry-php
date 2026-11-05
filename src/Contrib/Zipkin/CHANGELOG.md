# Changelog

Notable changes to `open-telemetry/exporter-zipkin`. Loosely follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/), with a Breaking changes section first so
upgraders can check them in one place.

## Unreleased

## 1.4.0 - 2026-11-06

### Breaking changes

- Requires PHP 8.2 ([#2091]).

### Deprecated

- This package, as the spec deprecates the Zipkin exporter. Zipkin accepts OTLP, so use
  `open-telemetry/exporter-otlp`. Creating the exporter from `OTEL_TRACES_EXPORTER=zipkin` triggers
  `E_USER_DEPRECATED` ([#2092]).

## 1.3.0 and earlier

See the [GitHub releases](https://github.com/opentelemetry-php/exporter-zipkin/releases).

[#2091]: https://github.com/open-telemetry/opentelemetry-php/pull/2091
[#2092]: https://github.com/open-telemetry/opentelemetry-php/pull/2092
