# AGENTS.md

Guidance for coding agents working on `open-telemetry/sem-conv`. The repository-wide `AGENTS.md` also applies.

## Generate the classes with the script

Every PHP file in this package comes from `script/semantic-conventions/semconv.sh`, run with `make semconv`. Don't
edit them by hand, even to fix a typo or restore a removed constant. A hand edit is lost at the next generation.

To change the output, change the script or its templates in `script/semantic-conventions/templates/`, then
regenerate. `script/semantic-conventions/README.md` lists the steps for a new semantic conventions version.

## Write one changelog entry per release

The package version matches the semantic conventions version it was generated from. Each release has a single
changelog entry, naming that version and linking its upstream release notes:

```markdown
### Changed

- Generated from [semantic conventions 1.45.0](https://github.com/open-telemetry/semantic-conventions/releases/tag/v1.45.0) ([#2111]).
```

Don't list breaking changes, deprecations, renames or new attributes. The upstream release notes already cover them.
