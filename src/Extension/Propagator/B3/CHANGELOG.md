# Changelog

Notable changes to `open-telemetry/extension-propagator-b3`. Loosely follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/), with a Breaking changes section first so
upgraders can check them in one place.

## Unreleased

### Breaking changes

- Conflicts with `open-telemetry/sdk` below 1.17. The `b3` and `b3multi` propagators are registered with the SDK
  `Registry` through SPI. Allow the `tbachert/spi` Composer plugin for the early registration, without it they are
  registered when the package's autoload file loads, as before ([#2120]).

## 1.3.0 - 2026-11-06

### Breaking changes

- Conflicts with `open-telemetry/sdk-configuration` below 0.10, which registers the same
  propagator providers. Upgrade it with this package ([#2107]).
- Requires PHP 8.2 ([#2091]) and `open-telemetry/api` 1.4 ([#2107]).

### Added

- The `b3` and `b3multi` propagator providers for declarative configuration, registered through
  SPI. They used to ship in `open-telemetry/sdk-configuration` ([#2107]).

## 1.2.0 and earlier

See the [GitHub releases](https://github.com/opentelemetry-php/extension-propagator-b3/releases).

[#2091]: https://github.com/open-telemetry/opentelemetry-php/pull/2091
[#2107]: https://github.com/open-telemetry/opentelemetry-php/pull/2107
[#2120]: https://github.com/open-telemetry/opentelemetry-php/pull/2120
