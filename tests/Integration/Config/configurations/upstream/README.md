# Upstream configuration files

Everything here is a **verbatim copy** from
[open-telemetry/opentelemetry-configuration](https://github.com/open-telemetry/opentelemetry-configuration):

- `snippets/` — self-contained files that each exercise one configuration type exhaustively.
- `examples/` — the complete example configurations upstream publishes for end users.

`ConfigurationTest::openTelemetryConfigurationDataProvider()` globs both, so every file is parsed
and used to build an SDK. Fixtures we maintain ourselves live in the parent directory.

Each file is also parsed as instrumentation configuration, which keeps only the
`instrumentation/development` root that the SDK parse discards. A file whose SDK configuration does
not parse has to be fixed, but one this SDK cannot parse as instrumentation configuration yet is
still synced, and listed in `ConfigurationTest::knownInstrumentationParseFailures()` with the reason
— currently the two experimental-instrumentation snippets and `otel-sdk-migration-config.yaml`.

## Keeping them in sync

**Do not update these by hand.** Bump `CONFIG_VERSION` in `script/config-snippets/sync.sh` and run
it. It fetches the tag, diffs it against these directories, and copies the files across. That
script's README documents the procedure, including what to do about a file that no longer parses
and about a bumped `file_format`.

Do not modify these files. That includes values that do not match this SDK.
`Resource_kitchen_sink.yaml` pins `schema_url` to `1.16.0` while the resource detectors emit a
later version, so parsing it logs a `Merging resources with different schema URLs` warning —
expected test output, not a failure. Leave it: the `script/semantic-conventions` procedure
deliberately skips this directory.

## New files

When a new file format adds files, ensure they can be parsed at a minimum. You may need to
implement a `ComponentProvider` if a new component is added. For a component the SDK does not
implement, a no-op provider registered via `composer.json`'s `extra.spi` is enough — see
`tests/Integration/Config/ComponentProvider/` for existing examples.
