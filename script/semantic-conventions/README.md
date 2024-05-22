# Semantic Convention generation script and templates

## Usage

To update semantic conventions, modify `semconv.sh`'s `SEMCONV_VERSION` and `GENERATOR_VERSION` values, then run:

```shell
./semconv.sh
```

## Backwards compatibility

If attributes have been removed in an update, you can add them back in via `templates/<class>_deprecations.php.partial` files,
the contents will be included in the generated output. Please remember to mark them as deprecated to discourage their future
use.

After generating new semantic conventions, you can locate removed attributes via:

```shell
diff <(grep "public const" src/SemConv/ResourceAttributes.php | sort -u) \
     <(git show main:src/SemConv/ResourceAttributes.php | grep "public const" | sort -u) \
     | grep '^>' \
     | grep -v SCHEMA_URL
```

```shell
diff <(grep "public const" src/SemConv/TraceAttributes.php | sort -u) \
     <(git show main:src/SemConv/TraceAttributes.php | grep "public const" | sort -u) \
     | grep '^>' \
     | grep -v SCHEMA_URL
```

Use this output as a basis for updating the relevant deprecations file and generate a second time to include them in the final output.

Note that some previously-removed semconv entries have been added back in recent versions, so may need to be removed from the
deprecations partials.

## Add to SemConv/Version

Add an entry to `src/SemConv/Version.php` for the new version.

## Update tests

Update `tests/Integration/Config/configurations/kitchen-sink.yaml`'s `resource.schema_url` value to the latest, as merging resources
with different schema URLs is a merging error, per spec.