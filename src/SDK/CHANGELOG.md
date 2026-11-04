# Changelog

Notable changes to `open-telemetry/sdk`. Loosely follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/), with a Breaking changes section first so
upgraders can check them in one place.

## Unreleased

### Breaking changes

- `OTEL_CONFIG_FILE` names one file. It used to be split on commas for instrumentation config
  ([#2105]).
- Requires PHP 8.2, and no longer requires `symfony/polyfill-php82` ([#2091]).
- Requires `open-telemetry/api` 1.11, for `Logging::setLogLevel()` ([#2068]).
- Requires `open-telemetry/sem-conv` 1.45, for `HttpIncubatingAttributes::HTTP_REQUEST_BODY_CONTENT`
  ([#2101]).

### Added

- `OTEL_PHP_EXCLUDED_REQUEST_HEADERS` disables the SDK for web requests that have a matching
  header, like `OTEL_PHP_EXCLUDED_URLS` does for a URL, for example
  `User-Agent: kube-probe*,X-Skip-Tracing`. Entries are `name: value` or `name`, the name ignores
  case and treats `_` as `-`, and the value takes `*` wildcards. A value cannot contain a comma,
  and a header sent more than once is matched as one value joined with `, ` ([#2113]).
- A request excluded by `OTEL_PHP_EXCLUDED_URLS` or `OTEL_PHP_EXCLUDED_REQUEST_HEADERS` still
  extracts the incoming trace context and sets up the propagators from `OTEL_PROPAGATORS`, so
  outgoing calls carry the caller's trace on, as with `OTEL_SDK_DISABLED`. It still produces no
  telemetry. This changes `OTEL_PHP_EXCLUDED_URLS`, which used to propagate nothing. Both
  settings are evaluated when the SDK autoloads, so they need an autoload per request and do not
  work in workers that autoload once at boot ([#2113]).
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

- `OTEL_ATTRIBUTE_COUNT_LIMIT` and `OTEL_ATTRIBUTE_VALUE_LENGTH_LIMIT` were ignored. They apply to
  span, event, link and log record limits that aren't set more specifically ([#2058]).
- `PsrTransportFactory` ignored its `cacert`, `cert` and `key` arguments. It passes them to the
  HTTP client ([#2060]). A client that rejects them is created without them, with a warning, and
  Symfony HttpClient no longer rejects them ([#2095]).
- A zero HTTP client timeout was dropped, or applied literally by Symfony HttpClient. It means no
  limit ([#2095], [#2105]).

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
[#2113]: https://github.com/open-telemetry/opentelemetry-php/pull/2113
