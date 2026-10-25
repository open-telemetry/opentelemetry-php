# Logs API

The [Logs API](https://github.com/open-telemetry/opentelemetry-specification/blob/v1.61.0/specification/logs/api.md) emits log records and events. It has two kinds
of user:

- Log appenders, which bridge an existing logging library such as Monolog into OpenTelemetry.
- Instrumentation and application code, which can emit log records and events directly.

## Emit a log record

Obtain a `Logger` from a `LoggerProviderInterface`, then build and emit a record with `Logger::logRecordBuilder()`.
Call `Logger::isEnabled()` first to skip expensive work when nothing would be recorded.

```php
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Logs\Severity;

$logger = Globals::loggerProvider()->getLogger('my-library');

if ($logger->isEnabled(severityNumber: Severity::INFO->value)) {
    $logger->logRecordBuilder()
        ->setSeverityNumber(Severity::INFO)
        ->setBody('user logged in')
        ->setAttribute('user.id', $userId)
        ->emit();
}
```

## Emit an event

An event is a log record with an event name. Its attributes and body follow the semantic conventions for that name.

```php
$logger->logRecordBuilder()
    ->setEventName('session.start')
    ->setAttribute('session.id', $sessionId)
    ->emit();
```

## Write a log appender

A log appender (sometimes called a handler) in a 3rd party logging library should:

- accept an `OpenTelemetry\API\Logs\LoggerProviderInterface`, or use the global one from
  `OpenTelemetry\API\Globals::loggerProvider()`
- obtain a `Logger` from the logger provider
- convert each log from its own format with `Logger::logRecordBuilder()`, then call `emit()`

See [monolog-otel-integration](/examples/logs/features/monolog-otel-integration.php) for an example.
