# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Setup

This project uses the "3 Musketeers" pattern: Docker + Make + Compose. Most commands run inside Docker containers.

```bash
cp .env.dist .env   # Required before first use
make install        # Install dependencies
```

Set `PHP_VERSION` (8.1, 8.2, 8.3, 8.4, 8.5) to test against specific PHP versions: `PHP_VERSION=8.1 make all`

Alternatively, `.devcontainer/` provides a devcontainer per PHP version. Inside one, `MAKEFLAGS=DC_RUN_PHP=` makes the
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

```bash
# Run a specific test file
vendor/bin/phpunit tests/Unit/SDK/Trace/SpanTest.php

# Run with a filter
vendor/bin/phpunit --filter testSomeMethod
```

Or via Docker: `docker compose run --rm php vendor/bin/phpunit tests/Unit/...`

## Architecture

This is a **monorepo** — packages are distributed to Packagist individually via git subtree splits (`.gitsplit.yml`).

### Package Structure (`/src`)

| Package | Purpose |
|---------|---------|
| `API/` | Core interfaces, noop implementations, late-binding providers |
| `SDK/` | Concrete implementations of the API (Trace, Metrics, Logs, Propagation, Resource) |
| `Context/` | Context storage, scope management, Fiber support |
| `Config/` | SDK configuration from environment/YAML using ComponentProvider pattern |
| `Contrib/` | OTLP exporters (gRPC, HTTP), Zipkin, gRPC transport |
| `Extension/` | B3 and CloudTrace propagators |
| `SemConv/` | Semantic convention attributes (auto-generated from OTel spec) |

### Key Architectural Patterns

- **API/SDK separation**: `API/` defines interfaces and noops; `SDK/` provides real implementations. User code should depend on API only.
- **SPI plugin discovery**: Plugins register via `composer.json` `extra.spi` for auto-discovery.
- **Late binding**: Providers can be registered after app startup; noop implementations handle the uninitialized state.
- **Architectural layers**: `deptrac.yaml` enforces that `API` cannot depend on `SDK`, `Contrib`, etc. Run `make deptrac` to validate.

### Test Layout

- `tests/Unit/` — Pure unit tests, mirrors `src/` structure
- `tests/Integration/` — Tests requiring external services (collector, Jaeger, Zipkin)
- `tests/TraceContext/` — W3C TraceContext compliance tests
- `tests/Benchmark/` — Performance benchmarks

### Signal Types

OpenTelemetry has three signals, each with parallel implementations:
- **Traces**: `API/Trace/`, `SDK/Trace/`
- **Metrics**: `API/Metrics/`, `SDK/Metrics/`
- **Logs**: `API/Logs/`, `SDK/Logs/`

### Exporters

Exporters live in `SDK/*/Export/` (built-in) or `Contrib/` (OTLP, Zipkin). OTLP supports both HTTP (JSON/Protobuf) and gRPC transports. Protobuf definitions are in `/proto/`.

## CI Requirements

PRs must pass `make all` cleanly:
1. `rector` — no refactoring suggestions
2. `style` — no code style violations
3. `deptrac` — no architectural layer violations
4. `phan`, `psalm`, `phpstan` — no static analysis errors
5. `test` — all tests pass with coverage metadata

## Mago

The project includes Mago (a PHP linter/fixer) checks in CI. These are currently **non-blocking** but configured via `.github/workflows/`.
