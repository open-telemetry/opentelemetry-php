<?php

declare(strict_types=1);

namespace OpenTelemetry\Example;

use OpenTelemetry\API\Logs\Severity;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Logs\EventLoggerProviderFactory;
use OpenTelemetry\SDK\Logs\LoggerProviderFactory;

require __DIR__ . '/../../../vendor/autoload.php';
putenv(sprintf('%s=console', Variables::OTEL_LOGS_EXPORTER));
putenv(sprintf('%s=simple', Variables::OTEL_PHP_LOGS_PROCESSOR));

$provider = (new EventLoggerProviderFactory())->create((new LoggerProviderFactory())->create());
$eventLogger = $provider->getEventLogger('my-event-logger', '1.0', 'https://example.com/events');

$payload = ['foo' => 'bar', 'baz' => 'bat', 'msg' => 'hello world'];
$eventLogger->emit(name: 'my-event', payload: $payload, severityNumber: Severity::DEBUG);
