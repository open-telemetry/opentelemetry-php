# Changelog

Notable changes to `open-telemetry/gen-otlp-protobuf`. Loosely follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/), with a Breaking changes section first so
upgraders can check them in one place.

The classes are generated from an `opentelemetry-proto` release with `make protobuf`. Changes to
the protocol itself are in the
[opentelemetry-proto changelog](https://github.com/open-telemetry/opentelemetry-proto/blob/main/CHANGELOG.md).

## Unreleased

## 1.11.1 - 2026-11-06

### Breaking changes

- Requires `google/protobuf` 4.31, and conflicts with `ext-protobuf` older than 4.31 ([#2110]).

### Changed

- Generated from `opentelemetry-proto` v1.11.1, up from v1.8.0. This adds the `Processcontext`
  messages ([#2110]).

## 1.10.0 and earlier

See the [GitHub releases](https://github.com/opentelemetry-php/gen-otlp-protobuf/releases).

[#2110]: https://github.com/open-telemetry/opentelemetry-php/pull/2110
