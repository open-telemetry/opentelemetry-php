# Semantic Convention generation script and templates

## Usage

To update semantic conventions, modify `semconv.sh`'s `SEMCONV_VERSION` and `OTEL_WEAVER_IMG_VERSION` values, then run:

```shell
make semconv
```

The script adds the new version to `src/SemConv/Version.php`, through `add-version.sh`.

## Backwards compatibility

Upstream doesn't remove conventions. It deprecates them, and the templates generate deprecated conventions with a
`@deprecated` tag. Don't filter them out with `exclude_deprecated` or `is deprecated` in the templates.

After generating, the script compares the public constants in each file with those before generation. It fails and
lists any that were removed. Fix the template if one caused it. If a constant has gone from the registry itself, add
it back in a `templates/registry/php/<class>_deprecations.php.partial` file, marked `@deprecated`. The template
includes the partial at the end of the class.

## Update tests

Update the `resource.schema_url` value in `tests/Integration/Config/configurations/resource.yaml`,
`resource-include-exclude.yaml` and `resource-default.yaml` to the latest, as merging resources
with different schema URLs is a merging error, per spec.

Leave the older `schema_url` values under `configurations/upstream/` alone; those files are verbatim
copies, synced only by `script/config-snippets/sync.sh`.