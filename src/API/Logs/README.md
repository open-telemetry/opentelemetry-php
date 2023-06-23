# Logs API

This `Logger` API is not designed to be used by application developers, but rather by library developers for the purpose
of integrating existing logging libraries with OpenTelemetry.

## Logging from 3rd party loggers

3rd party loggers should log to OpenTelemetry in accordance with the
[logs bridge API](https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/logs/bridge-api.md)
specification.

This means that a "log appender" in the 3rd party logging library (sometimes known as a "handler") should:
- accept an `OpenTelemetry\API\Logs\LoggerProviderInterface`, or obtain a globally registered one from `OpenTelemetry\API\Instrumentation\Globals`
- obtain a `Logger` from the logger provider (optionally adding any resources that should be associated with logs emitted)
- convert logs from its own log format into OpenTelemetry's `LogRecord` format
- send the logs to OpenTelemetry via `Logger::logRecord()`

See [monolog-otel-integration](/examples/logs/features/monolog-otel-integration.php) for an example.

