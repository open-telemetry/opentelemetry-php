# Changelog

Notable changes to `open-telemetry/sdk`. Loosely follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/), with a Breaking changes section first so
upgraders can check them in one place.

## Unreleased

### Breaking changes

- `Registry` now gets its built-in and package components from `RegistrarInterface` providers (SPI) when it is first
  asked for one, no longer from `_register.php` files that Composer includes. A component registered with
  `Registry::register*()` before that first lookup wins over a provider's, one registered with `$clobber` always wins,
  and a non-clobbering registration made after it is ignored for names a provider already filled. A package that
  registers a built-in name from its own autoload file now wins over the SDK's built-in one, it used to lose. Allow the
  `tbachert/spi` Composer plugin in `config.allow-plugins` for this to take effect, without it the components are
  registered when their autoload files load, as before ([#2120]).

### Added

- `RegistrarInterface`, for packages to register exporter factories, transport factories and propagators with the
  `Registry` through SPI. Declare it with `extra.spi-config.autoload-files` or `extra.spi`. It is a bridge for SDK 1.x
  and will be removed in SDK 2.0, together with the `Registry` ([#2120]).
- `InMemoryExporter` accepts an `AggregationTemporalitySelectorInterface`, such as `TemporalityPreference`, as its
  temporality, like the console and OTLP metric exporters ([#2129]).
- An asynchronous instrument callback that returns a value logs a warning, once per callback. The value is ignored,
  as it always was. Report measurements through the observer instead ([#2129]).

### Changed

- `InstrumentType::GAUGE` is no longer experimental. The synchronous Gauge is stable in the specification ([#2129]).

### Deprecated

- Passing a `Temporality` or a string as the temporality of `ConsoleMetricExporter` or `InMemoryExporter`. Pass a
  selector such as `TemporalityPreference::cumulative()` or `TemporalityPreference::delta()`. The delta preference
  keeps UpDownCounters cumulative, as the specification requires, where `Temporality::DELTA` made them delta. An
  exporter built without a temporality is not deprecated. It uses each stream's temporality until 2.0, which is
  delta for synchronous instruments and cumulative for asynchronous ones. From 2.0 it will use cumulative. Pass
  `TemporalityPreference::cumulative()` to get that now ([#2129]).
- `MetricMetadataInterface::temporality()` and `MetricStreamInterface::temporality()`. Choose temporality by
  instrument type instead. They will be removed in 2.0 ([#2129]).

### Fixed

- With the root span enabled and the `tbachert/spi` plugin running, the SDK finds the OTLP factories while
  `sdk/_autoload.php` builds its providers. It used to build them before `exporter-otlp/_register.php` could register
  them, and the providers then stayed noop for the whole process, with `Span exporter factory not defined for: otlp`
  in the log. This also hit `OTEL_CONFIG_FILE` (`Transport factory not defined for protocol: http`) ([#2120]).
- `AutoRootSpan::start($notifySpanEnded)` registers the callback also when the root span was already started, for
  example by `SdkAutoloader::autoload()`. The callback used to be dropped. A callback that throws no longer keeps the
  root span from ending ([#2120]).
- Consistent probability samplers compute the `th` rejection threshold exactly, with the precision recommended by the
  specification. Sampling thresholds computed from ratios that aren't a sum of 56-bit fractions used to differ from the
  spec test vectors: `1/3` was `th:aaaaaaaaaaaaac` instead of `th:aaab`, `0.1` was `th:e6666666666666` instead of
  `th:e666` ([#2140]).

## 1.16.1 - 2026-11-06

### Security

- The host detector ran `ioreg`, `kenv` and `powershell.exe` through a shell, so they were
  looked up on `PATH`, and on Windows in the current directory. A program placed there ran
  instead. It runs `/usr/sbin/ioreg`, `/bin/kenv` and `reg.exe` by absolute path, without a
  shell ([GHSA-fc6f-cp96-6g5w]).
- When the host id lookup failed, `host.id` was `""` on macOS and Windows, or `/bin/kenv` on
  BSD. It is left out instead ([GHSA-fc6f-cp96-6g5w]).

## 1.16.0 - 2026-11-06

### Breaking changes

- `OTEL_CONFIG_FILE` names one file. It used to be split on commas for instrumentation config
  ([#2105]).
- Requires PHP 8.2, and no longer requires `symfony/polyfill-php82` ([#2091]).
- Requires `open-telemetry/api` 1.11, for `Logging::setLogLevel()` ([#2068]).
- Requires `open-telemetry/sem-conv` 1.45, for `HttpIncubatingAttributes::HTTP_REQUEST_BODY_CONTENT`
  ([#2101]).
- If you set an `Accept-Encoding` header in the exporter headers, only `gzip` responses are decoded.
  The discovered Guzzle client used to decode responses, and no longer does, so the body of a response
  in another encoding, such as `deflate` or `zstd`, is ignored with a warning. Without that header the
  SDK asks for an uncompressed response ([#2115], [#2112]).
- `PsrTransport` only decodes gzip responses (also as `x-gzip`). The body of a successful response
  that can't be decoded, such as a `deflate` or `br` one that the specification doesn't mention, is
  ignored with a warning ([#2112]).

### Added

- `OTEL_PHP_EXCLUDED_REQUEST_HEADERS` disables the SDK for web requests that have a matching
  header, like `OTEL_PHP_EXCLUDED_URLS` does for a URL, for example
  `User-Agent: kube-probe*,X-Skip-Tracing`. Entries are `name: value` or `name`, the name ignores
  case and treats `_` as `-`, and the value takes `*` wildcards. A value cannot contain a comma,
  and a header sent more than once is matched as one value joined with `, `. Headers are read from
  `$_SERVER` and are set by the client, so use a header that a trusted proxy sets or removes if
  clients must not be able to opt out. It is evaluated when the SDK autoloads, so it needs an
  autoload per request and does not work in workers that autoload once at boot ([#2113]).
- Composable samplers in `OpenTelemetry\SDK\Trace\Sampler\Consistent`, led by
  `CompositeSampler`. All are experimental ([#2077]).
- The auto root span names spans `METHOD path`, grouped by `OTEL_PHP_TRANSACTION_URL_GROUPS`. It
  captures the headers named in `OTEL_INSTRUMENTATION_HTTP_SERVER_CAPTURE_REQUEST_HEADERS` and
  `OTEL_INSTRUMENTATION_HTTP_SERVER_CAPTURE_RESPONSE_HEADERS`, and sets
  `http.response.status_code`. `OTEL_PHP_TRANSACTION_SPAN_ENABLED` turns it on, and
  `OTEL_PHP_TRANSACTION_SPAN_ENABLED_CLI` adds a root span for CLI scripts. New
  `AutoRootSpan::start()` ([#2080]).
- `OTEL_METRICS_EXEMPLAR_FILTER` accepts `always_on`, `always_off` and `trace_based`, and defaults
  to `trace_based`. `all`, `none` and `with_sampled_trace` still work ([#2057]).
- `RandomTraceIdGeneratorInterface`. Root spans from a generator that implements it, such as
  `RandomIdGenerator`, get the W3C `random` trace flag ([#2092]).
- `TemporalityPreference`, an `AggregationTemporalitySelectorInterface` that picks temporality per
  instrument kind ([#2095]), and `TemporalityPreference::fromName()` ([#2105]).
- `DropAggregation`, `InstrumentUnitCriteria`, `ViewTemplate::withAggregationResolver()`, and
  `recordMinMax` on `ExplicitBucketHistogramAggregation` ([#2093]).
- `ViewTemplate::withAttributeKeyFilter()`, which keeps attribute keys by `*` and `?` patterns.
  Excluded wins ([#2095]).
- `ConsoleMetricExporter` accepts an `AggregationTemporalitySelectorInterface` and a
  `TransportInterface` ([#2095]).
- `SdkBuilder::setLogLevel()`, applied by `buildAndRegisterGlobal()` until its scope is detached
  ([#2068]).
- `OTEL_PHP_INSTRUMENTATION_HTTP_SERVER_CAPTURE_REQUEST_BODY` makes the auto root span record the
  request body as `http.request.body.content`, cut at
  `OTEL_PHP_INSTRUMENTATION_HTTP_SERVER_CAPTURE_REQUEST_BODY_MAX_SIZE` bytes (1024 by default).
  It is off by default, records only textual bodies, and does not redact them ([#2101]).

### Changed

- An unparseable `OTEL_CONFIG_FILE`, or one set without `open-telemetry/sdk-configuration`
  installed, logs a warning and leaves the SDK as noop. It used to throw ([#2076]).
- Instrumentation config is read from the same parse of `OTEL_CONFIG_FILE` as the SDK. This needs
  `open-telemetry/sdk-configuration` 0.10. An older one logs a warning and instrumentation gets no
  config ([#2076]).
- A malformed `OTEL_RESOURCE_ATTRIBUTES` logs a warning and is discarded in full. It used to
  throw, which left the SDK as noop ([#2105]).
- A trailing or doubled comma in a map variable such as `OTEL_RESOURCE_ATTRIBUTES` or
  `OTEL_EXPORTER_OTLP_HEADERS` is skipped. An entry with an empty key is an error ([#2105]).
- `BatchSpanProcessor` and `BatchLogRecordProcessor` lower a `maxExportBatchSize` above
  `maxQueueSize` to the queue size, with a warning. They used to throw ([#2105]). They accept a
  schedule delay or export timeout of `0` ([#2095]).
- `TraceIdRatioBasedSampler` warns once when used as a child sampler ([#2105]). Its description is
  `TraceIdRatioBased{...}`, not `TraceIdRatioBasedSampler{...}` ([#2092]).
- `AlwaysRecordSampler` is no longer experimental ([#2092]).
- Exception attributes set explicitly on a log record win over those from `setException()`
  ([#2092]).
- HTTP export retries 429, 502, 503 and 504. It used to retry 408, 429 and every 5xx ([#2061]).
- `Instrument::$unit` and `$description` are strings. A null is stored as `''` ([#2105]).
- `ConsoleMetricExporter` writes through a stream transport, and returns false if the metrics
  can't be encoded. It writes NaN and infinities as `"NaN"`, `"Infinity"` and `"-Infinity"`
  ([#2095]).
- The console exporters' output format is unspecified and may change between releases ([#2092]).
- `SdkDistribution` and `DistributionRegistry` are `@internal`. `DistributionConfiguration` and
  `DistributionProperties` are experimental ([#2097]).

### Deprecated

- `OTEL_PHP_EXPERIMENTAL_AUTO_ROOT_SPAN`. Use `OTEL_PHP_TRANSACTION_SPAN_ENABLED`, which wins when
  both are set ([#2080]).
- `EventLoggerProvider`, `EventLoggerProviderInterface` and `NoopEventLoggerProvider`, with the
  rest of the Events API ([#2092]).

### Fixed

- Every export failed when the HTTP client decoded a gzip response itself and left `Content-Encoding`
  in place, as Symfony HttpClient does, because the body was decoded again. `PsrTransport` now sends
  `Accept-Encoding: identity`, so the server doesn't compress the response, and the discovered Guzzle
  client no longer decodes responses (`decode_content` is `false`). A client you pass in yourself keeps
  its own decoding, but gets the same `Accept-Encoding` header ([#2115]).
- `PsrTransport` limits the OTLP/HTTP response body to 4 MiB, also after decompression, as the
  specification requires. A larger response is discarded with a warning and the export fails without
  a retry, whatever its status, even if the server accepted the data. Decompression is streamed and
  checked in small steps. The PSR-18 client may buffer the whole response before the limit applies
  ([#2112]).
- `OTEL_ATTRIBUTE_COUNT_LIMIT` and `OTEL_ATTRIBUTE_VALUE_LENGTH_LIMIT` were ignored. They apply to
  span, event, link and log record limits that aren't set more specifically ([#2058]).
- `PsrTransportFactory` ignored its `cacert`, `cert` and `key` arguments. It passes them to the
  HTTP client ([#2060]). A client that rejects them is created without them, with a warning, and
  Symfony HttpClient no longer rejects them ([#2095]).
- A zero HTTP client timeout was dropped, or applied literally by Symfony HttpClient. It means no
  limit ([#2095], [#2105]).
- The wildcard matcher behind `OTEL_PHP_TRANSACTION_URL_GROUPS` matched texts it should not: a
  pattern with several `*` accepted its parts in the wrong order (`*health*probe*` matched
  `client/probe/health`) and the last part could overlap an earlier one (`ab*bc` matched `abc`)
  ([#2113]).

## 1.15.0 and earlier

See the [GitHub releases](https://github.com/opentelemetry-php/sdk/releases).

[#2057]: https://github.com/open-telemetry/opentelemetry-php/pull/2057
[#2058]: https://github.com/open-telemetry/opentelemetry-php/pull/2058
[#2060]: https://github.com/open-telemetry/opentelemetry-php/pull/2060
[#2061]: https://github.com/open-telemetry/opentelemetry-php/pull/2061
[#2068]: https://github.com/open-telemetry/opentelemetry-php/pull/2068
[#2076]: https://github.com/open-telemetry/opentelemetry-php/pull/2076
[#2077]: https://github.com/open-telemetry/opentelemetry-php/pull/2077
[#2080]: https://github.com/open-telemetry/opentelemetry-php/pull/2080
[#2091]: https://github.com/open-telemetry/opentelemetry-php/pull/2091
[#2092]: https://github.com/open-telemetry/opentelemetry-php/pull/2092
[#2093]: https://github.com/open-telemetry/opentelemetry-php/pull/2093
[#2095]: https://github.com/open-telemetry/opentelemetry-php/pull/2095
[#2097]: https://github.com/open-telemetry/opentelemetry-php/pull/2097
[#2101]: https://github.com/open-telemetry/opentelemetry-php/pull/2101
[#2105]: https://github.com/open-telemetry/opentelemetry-php/pull/2105
[#2112]: https://github.com/open-telemetry/opentelemetry-php/pull/2112
[#2113]: https://github.com/open-telemetry/opentelemetry-php/pull/2113
[#2115]: https://github.com/open-telemetry/opentelemetry-php/pull/2115
[#2120]: https://github.com/open-telemetry/opentelemetry-php/pull/2120
[#2140]: https://github.com/open-telemetry/opentelemetry-php/pull/2140
[#2129]: https://github.com/open-telemetry/opentelemetry-php/pull/2129
[#2140]: https://github.com/open-telemetry/opentelemetry-php/pull/2140
[GHSA-fc6f-cp96-6g5w]: https://github.com/open-telemetry/opentelemetry-php/security/advisories/GHSA-fc6f-cp96-6g5w
