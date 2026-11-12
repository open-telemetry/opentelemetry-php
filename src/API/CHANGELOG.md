# Changelog

Notable changes to `open-telemetry/api`. Loosely follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/), with a Breaking changes section first so
upgraders can check them in one place.

## Unreleased

### Fixed

- `WithSpanHandler` uses the span name of `#[WithSpan(span_name: '...')]`. Only the positional form
  `#[WithSpan('...')]` set it; the named form fell back to `Class::method` ([#2119]).
- An unrecognised `OTEL_LOG_LEVEL` value logs a warning and falls back to `info`. It used to fall
  back to `info` silently ([#2125]).

## 1.11.0 - 2026-11-06

### Breaking changes

- Conflicts with `open-telemetry/sdk-configuration` below 0.10, which registers the same
  propagator providers. Upgrade it with this package ([#2107]).
- `ConfigProviderInterface::getInstrumentationConfig()` takes an optional `Context`. Implementations
  must add the parameter. The interface is experimental ([#2076]).
- Requires PHP 8.2, and no longer requires `symfony/polyfill-php82` ([#2091]).

### Added

- The `tracecontext` and `baggage` propagator providers for declarative configuration, registered
  through SPI. They used to ship in `open-telemetry/sdk-configuration` ([#2107]).
- `Severity::fromName()` and `Severity::toPsr3()` ([#2068]).
- `Context::withLogger()` ([#2068]).
- `Logging::setLogLevel()`, which takes precedence over `OTEL_LOG_LEVEL`, and
  `Logging::restoreLogLevel()` ([#2068]).
- `symfony/config` is suggested, for implementing a `ComponentProvider` ([#2097]).

### Changed

- `Context`'s default logger writes to the internal logger, filtered by `OTEL_LOG_LEVEL`. It was a
  `NullLogger` ([#2068]).
- `trace()` and `WithSpanHandler` no longer set `exception.escaped`, which semantic conventions
  deprecated ([#2092]).
- `TracerInterface::isEnabled()` and `Instrument::isEnabled()` are no longer experimental
  ([#2092]).
- These are marked experimental: `ConfigProviderInterface`, `NoopConfigProvider` and
  `NoopConfigProperties` ([#2076]), `ConfigProperties` and `ConfigurationRegistry` ([#2093]),
  `HttpConfig` and `PeerConfig` ([#2097]).
- Each `SystemClock` has its own reference time. It used to be shared by all instances ([#2041]).
- A log writer that throws no longer breaks the code that logs. `LogsMessagesTrait` swallows the
  exception ([#2101]).

### Deprecated

- `NoopEventLogger` and `NoopEventLoggerProvider`, with the rest of the Events API ([#2092]).

## 1.10.1 and earlier

See the [GitHub releases](https://github.com/opentelemetry-php/api/releases).

[#2041]: https://github.com/open-telemetry/opentelemetry-php/pull/2041
[#2068]: https://github.com/open-telemetry/opentelemetry-php/pull/2068
[#2076]: https://github.com/open-telemetry/opentelemetry-php/pull/2076
[#2091]: https://github.com/open-telemetry/opentelemetry-php/pull/2091
[#2092]: https://github.com/open-telemetry/opentelemetry-php/pull/2092
[#2093]: https://github.com/open-telemetry/opentelemetry-php/pull/2093
[#2097]: https://github.com/open-telemetry/opentelemetry-php/pull/2097
[#2101]: https://github.com/open-telemetry/opentelemetry-php/pull/2101
[#2107]: https://github.com/open-telemetry/opentelemetry-php/pull/2107
[#2119]: https://github.com/open-telemetry/opentelemetry-php/pull/2119
[#2125]: https://github.com/open-telemetry/opentelemetry-php/pull/2125
