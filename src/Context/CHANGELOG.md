# Changelog

Notable changes to `open-telemetry/context`. Loosely follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/), with a Breaking changes section first so
upgraders can check them in one place.

## Unreleased

## 1.6.0 - 2026-11-06

### Breaking changes

- Requires PHP 8.2, and no longer requires `symfony/polyfill-php82` ([#2091]).

### Changed

- `EnvironmentGetterSetter` normalizes keys to environment variable names. Letters are
  uppercased, any other character outside `A-Z`, `0-9` and `_` becomes `_`, and a leading digit
  gets a `_` prefix. An empty key is written as `_`, where it used to throw ([#2092]).
- `EnvironmentGetterSetter::keys()` returns the names of variables that are already normalized, as
  they are. It used to return every variable name, lowercased ([#2092]).

## 1.5.0 and earlier

See the [GitHub releases](https://github.com/opentelemetry-php/context/releases).

[#2091]: https://github.com/open-telemetry/opentelemetry-php/pull/2091
[#2092]: https://github.com/open-telemetry/opentelemetry-php/pull/2092
