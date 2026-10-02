# Declarative configuration upstream sync

Syncs `tests/Integration/Config/configurations/upstream/` with the `snippets/` and `examples/`
directories of a tagged
[opentelemetry-configuration](https://github.com/open-telemetry/opentelemetry-configuration/releases)
release. Everything under `upstream/` is a verbatim copy; the fixtures we maintain ourselves sit
one level up, alongside it.

## Usage

Modify `sync.sh`'s `CONFIG_VERSION`, then run:

```shell
./sync.sh
```

It clones the tag into `var/`, then for each directory reports files added upstream, files removed
upstream, and content changes to existing ones, before copying them across. Acting on that report
is manual.

## Then

```shell
make test-integration
```

Every copied file must parse. What to expect:

- `Unrecognized option "…"` — a new key or component. Add a `ComponentProvider`; a no-op is
  enough for a component the SDK does not implement, and
  `tests/Integration/Config/ComponentProvider/` has examples. Do not drop the file.
- `file_format "1.x" is newer than …` (a warning, not a failure) — a minor bump. The files still
  parse, but anything the new minor introduces is ignored until implemented. Bump
  `FILE_FORMAT_MINOR` in `src/Config/SDK/ComponentProvider/OpenTelemetrySdk.php` once it is.
- `unsupported version …` — only a major bump, which this SDK does not accept. It needs
  `FILE_FORMAT_MAJOR` raised alongside the package's own major version, and affects downstream
  config files and contrib. See `src/Config/SDK/README.md`.

If a file cannot be made to parse yet, keep it and add it to
`ConfigurationTest::knownParseFailures()` with the expected message and the reason. It is then
asserted to fail, so the gap is tracked and fixing the blocker tells you to remove the entry.
