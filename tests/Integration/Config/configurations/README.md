# Configuration test fixtures

The `.yaml` files in this directory are **ours**, written to cover behaviour upstream's files do
not. Edit them freely. Everything under `upstream/` is a verbatim copy of
opentelemetry-configuration and must not be hand-edited — see that directory's README.

Most of these back a single assertion-based test in `ConfigurationTest`; the test name says what
each one is for. Two are parsed without assertions, as a check that they remain valid:

| File | Covers |
|------|--------|
| `anchors.yaml` | YAML anchors and merge keys (`<<:`), which no upstream file uses |
| `php-specific.yaml` | `response_propagator/development` under `distribution`, a PHP extension absent from the schema |
| `distribution.yaml` | `distribution`, which no upstream file uses |

When adding a fixture, prefer asserting on the built SDK over parse-only coverage: an upstream file
probably already proves the keys parse.

Every `.yaml` here is also parsed by both YAML backends and asserted to produce identical results
(`tests/Unit/.../Loader/YamlBackendParityTest.php`). ext-yaml follows YAML 1.1 scalar typing and
symfony/yaml the 1.2 core schema, so avoid forms they read differently — `yes`/`on`, `017`, `0o17`,
`1:30`, bare dates, out-of-range integers. Quote the value, or use `true`/`false`.
