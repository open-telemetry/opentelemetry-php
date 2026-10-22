# AGENTS.md

Guidance for coding agents working in this repository.

## Specification

This is the PHP implementation of the [OpenTelemetry specification](https://opentelemetry.io/docs/specs/otel/).
Changes must conform to it. Check the relevant section of the spec before changing behaviour, and follow its naming,
defaults and environment variables. Settings the spec doesn't cover use the `OTEL_PHP_` prefix. Declarative
configuration follows the [opentelemetry-configuration](https://github.com/open-telemetry/opentelemetry-configuration)
schema.

### Link to the spec

A link lets a reader check the code against the rule it implements. Add one when the spec decides the outcome and
the code alone doesn't show why:

- Code that implements a spec rule, such as a required format, default, name or precedence. Use an `@see` in the
  docblock.
- A test that confirms a spec rule. Put the `@see` on the test method, after any `@param` lines.
- A deprecation or removal the spec requires, in the docblock, the deprecation message and the README.
- A commit or PR that implements a spec change. Reference each section it addresses.

Don't link when the spec doesn't cover the code, such as internal helpers, PHP-specific plumbing or settings with the
`OTEL_PHP_` prefix. Don't link to show that a class merely exists in the spec.

Link to the spec repository, pinned to the release you checked against, for example
`https://github.com/open-telemetry/opentelemetry-specification/blob/v1.61.0/specification/trace/sdk.md#alwaysrecord`.
Don't link to opentelemetry.io, `main` or blog posts, because their text changes after the code is written. Link the
most specific heading, and check the anchor exists at that tag.

Leave existing links pinned to older releases alone, unless the change brings that item up to a newer release.

## Development Setup

This project uses the "3 Musketeers" pattern: Docker + Make + Compose. Most commands run inside Docker containers.

```bash
cp .env.dist .env   # Required before first use
make install        # Install dependencies
```

Set `PHP_VERSION` (8.1, 8.2, 8.3, 8.4, 8.5) to test against specific PHP versions: `PHP_VERSION=8.1 make all`

PHP 8.1 is the minimum supported version and the Makefile default, so code must not use newer syntax or functions.
Polyfills for 8.2 to 8.4 are available.

Most contributors use the Makefile with Docker Compose. Alternatively, `.devcontainer/` provides a devcontainer per
PHP version. Inside one, `MAKEFLAGS=DC_RUN_PHP=` makes the
`make` targets run tools directly rather than via `docker compose run`. See `.devcontainer/README.md`.

## Common Commands

```bash
make test           # Unit + integration tests
make test-unit      # Unit tests only
make test-integration # Integration tests only
make style          # Run php-cs-fixer (auto-fix)
make rector-write   # Apply rector refactoring
make deptrac        # Check architectural layer dependencies
make phan           # Phan static analysis
make psalm          # Psalm static analysis
make phpstan        # PHPStan static analysis
make all            # Full CI suite (run before submitting PRs)
make all-lowest     # Same but with lowest dependency versions
```

### Running a single test

Use the developer image rather than any PHP installed locally, so the PHP version and extensions match CI. `.env.dist`
enables Xdebug, which slows tests down, so turn it off as the Makefile does:

```bash
docker compose run --rm php env XDEBUG_MODE=off vendor/bin/phpunit tests/Unit/SDK/Trace/SpanTest.php
docker compose run --rm php env XDEBUG_MODE=off vendor/bin/phpunit --filter testSomeMethod
```

Inside a devcontainer, run `vendor/bin/phpunit` directly.

## Architecture

This is a **monorepo** — packages are distributed to Packagist individually via git subtree splits (`.gitsplit.yml`).

### Package Structure (`/src`)

| Package | Purpose |
|---------|---------|
| `API/` | Core interfaces, noop implementations, late-binding providers |
| `SDK/` | Concrete implementations of the API (Trace, Metrics, Logs, Propagation, Resource) |
| `Context/` | Context storage, scope management, Fiber support |
| `Config/SDK/` | Declarative (YAML) SDK configuration using the ComponentProvider pattern |
| `Contrib/` | OTLP exporters (gRPC, HTTP), Zipkin, gRPC transport |
| `Extension/` | B3 propagator |
| `SemConv/` | Semantic convention attributes (auto-generated from OTel spec) |

The generated protobuf classes in `proto/otel/` are a split package too.

### Key Architectural Patterns

- **API/SDK separation**: `API/` defines interfaces and noops; `SDK/` provides real implementations. User code should depend on API only.
- **SPI plugin discovery**: Plugins register via `composer.json` `extra.spi` for auto-discovery. The root
  `composer.json` repeats every package's `extra.spi` entries and `autoload.files` (the `_register.php` files), so a
  new ComponentProvider or exporter factory goes in both the package's `composer.json` and the root one.
- **Late binding**: Providers can be registered after app startup; noop implementations handle the uninitialized state.
- **Architectural layers**: `deptrac.yaml` enforces that `API` cannot depend on `SDK`, `Contrib`, etc. Run `make deptrac` to validate.

### Test Layout

- `tests/Unit/` — Pure unit tests, mirrors `src/` structure
- `tests/Integration/` — Tests that exercise several components together: config files, SPI wiring, Fiber `.phpt`
  tests. They need no external services.
- `tests/TraceContext/` — W3C TraceContext compliance tests
- `tests/Benchmark/` — Performance benchmarks

### Writing tests

- `phpunit.xml.dist` sets `requireCoverageMetadata`, so every test class needs `#[CoversClass]`,
  `#[CoversFunction]` or `#[CoversNothing]`.
- Tests run in random order. A test that changes globals or environment variables should `use TestState`
  (`tests/TestState.php`) and set variables with `setEnvironmentVariable()`, which restores them afterwards.
- Reproduce an order-dependent failure with `--random-order-seed=<seed>`, using the seed from the failing run.

### Signal Types

OpenTelemetry has three signals, each with parallel implementations:
- **Traces**: `API/Trace/`, `SDK/Trace/`
- **Metrics**: `API/Metrics/`, `SDK/Metrics/`
- **Logs**: `API/Logs/`, `SDK/Logs/`

### Exporters and transports

These are separate layers:

- An exporter belongs to one signal and turns its data into a wire format. It implements the signal's exporter
  interface, such as `SpanExporterInterface`. The SDK has console, in-memory and noop exporters for each signal, in
  `SDK/Trace/SpanExporter/`, `SDK/Metrics/MetricExporter/` and `SDK/Logs/Exporter/`. `Contrib/` has the OTLP
  exporters for all three signals, and Zipkin for traces.
- A transport sends the encoded bytes and knows nothing about signals. It implements `TransportInterface` in
  `SDK/Common/Export/`. Transport factories are registered by protocol name: `http` (PSR-18, in `Contrib/Otlp`),
  `grpc` (`Contrib/Grpc`) and `stream` (`SDK/Common/Export/Stream`).

An OTLP exporter picks its content type (protobuf, JSON or NDJSON) and its transport from
`OTEL_EXPORTER_OTLP_PROTOCOL`. The generated protobuf classes are in `/proto/`.

### Generated code

Files marked "DO NOT EDIT" are generated. Don't edit them by hand:
- `src/SemConv/`: `SEMCONV_VERSION=x.y.z make semconv`
- `proto/otel/`: `make protobuf`

## CI Requirements

The blocking checks in `.github/workflows/php.yml` run on PHP 8.1 to 8.5:
1. `composer validate` and `make packages-composer`
2. `make style`: CI runs it with `--dry-run`, so run it locally to apply fixes
3. `make deptrac`: no architectural layer violations
4. `make psr4`: namespaces match the PSR-4 autoload rules
5. `make phpstan`: no static analysis errors
6. `make test`: unit and integration tests pass
7. `make spi`: no outdated SPI package dependencies

Phan and Psalm run in CI but don't block, and Rector isn't run in CI. `make all-checks` runs all of the above plus
`rector`, `phan` and `psalm`.

Fix new static analysis errors in the code. Add them to `phpstan-baseline.neon` or `deptrac.baseline.yaml` only as
a last resort, and explain why in the PR.

## Mago

The project includes Mago (a PHP linter/fixer) checks in CI. These are currently **non-blocking** but configured via `.github/workflows/`.
