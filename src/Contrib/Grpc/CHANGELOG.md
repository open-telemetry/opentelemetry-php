# Changelog

Notable changes to `open-telemetry/transport-grpc`. Loosely follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/), with a Breaking changes section first so
upgraders can check them in one place.

## Unreleased

### Breaking changes

- Requires `open-telemetry/sdk` 1.17 and `tbachert/spi`. The `grpc` transport factory is registered through SPI, so
  the `tbachert/spi` Composer plugin must be allowed ([#2120]).

## 1.3.0 - 2026-11-06

### Breaking changes

- Requires PHP 8.2 ([#2091]).

### Fixed

- A timeout of `0` set a deadline that had already passed, so every export failed. It means no
  deadline ([#2095]).

## 1.2.0 and earlier

See the [GitHub releases](https://github.com/opentelemetry-php/transport-grpc/releases).

[#2091]: https://github.com/open-telemetry/opentelemetry-php/pull/2091
[#2095]: https://github.com/open-telemetry/opentelemetry-php/pull/2095
[#2120]: https://github.com/open-telemetry/opentelemetry-php/pull/2120
