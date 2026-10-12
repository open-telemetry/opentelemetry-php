# Configuration test fixtures

The `.yaml` files in this directory are **ours**, written to cover behaviour upstream's files do
not. Edit them freely. Everything under `upstream/` is a verbatim copy of
opentelemetry-configuration and must not be hand-edited — see that directory's README.

Most of these back a single assertion-based test in `ConfigurationTest`; the test name says what
each one is for. Two are parsed without assertions, as a check that they remain valid:

| File | Covers |
|------|--------|
| `anchors.yaml` | YAML anchors and merge keys (`<<:`), which no upstream file uses |
| `php-specific.yaml` | `response_propagator/development`, a PHP extension absent from the schema |
| `distribution.yaml` | `distribution`, which no upstream file uses |

When adding a fixture, prefer asserting on the built SDK over parse-only coverage: an upstream file
probably already proves the keys parse.
