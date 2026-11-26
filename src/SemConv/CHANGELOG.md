# Changelog

Notable changes to `open-telemetry/sem-conv`. Loosely follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/), with a Breaking changes section first so
upgraders can check them in one place.

The classes are generated from a semantic conventions release with `make semconv`. Changes to the
conventions themselves are in the
[semantic conventions changelog](https://github.com/open-telemetry/semantic-conventions/blob/main/CHANGELOG.md).

## Unreleased

### Fixed

- Restore the constants for deprecated conventions, which 1.45.0 removed. They are back, marked `@deprecated`, with
  their replacement where upstream names one ([#2139]).

[#2139]: https://github.com/open-telemetry/opentelemetry-php/issues/2139

## 1.45.0 - 2026-11-04

### Changed

- Generated from [semantic conventions 1.45.0](https://github.com/open-telemetry/semantic-conventions/releases/tag/v1.45.0) ([#2111]).

[#2111]: https://github.com/open-telemetry/opentelemetry-php/pull/2111

## 1.44.0 and earlier

See the [GitHub releases](https://github.com/opentelemetry-php/sem-conv/releases).
