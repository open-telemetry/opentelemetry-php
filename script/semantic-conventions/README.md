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

After generating new sementic conventions, you can locate removed attributes via:

```shell
diff <(grep "public const" src/SemConv/ResourceAttributes.php | sort -u) \
     <(git show main:src/SemConv/ResourceAttributes.php | grep "public const" | sort -u) \
     | grep '^>' \
     | grep -v SCHEMA_URL
```

Use this output as a basis for updating the relevant deprecations file and generate a second time to include them in the final output.
