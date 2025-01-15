<?php

declare(strict_types=1);

namespace OpenTelemetry\Example;

use OpenTelemetry\API\Common\Time\Clock;
use OpenTelemetry\API\Logs\Severity;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactory;
use OpenTelemetry\SDK\Logs\Exporter\ConsoleExporterFactory;
use OpenTelemetry\SDK\Logs\LoggerProvider;
use OpenTelemetry\SDK\Logs\Processor\BatchLogRecordProcessor;

require __DIR__ . '/../../../vendor/autoload.php';

$loggerProvider = new LoggerProvider(
    new BatchLogRecordProcessor(
        (new ConsoleExporterFactory())->create(),
        Clock::getDefault()
    ),
    new InstrumentationScopeFactory(Attributes::factory())
);
//get a logger, and emit an event.
$loggerOne = $loggerProvider->getLogger('demo', '1.0');
$loggerTwo = $loggerProvider->getLogger('demo', '2.0');

$payload = ['foo' => 'bar', 'baz' => 'bat', 'msg' => 'hello world'];

$loggerOne->emitEvent(name: 'foo', severityNumber: Severity::INFO, body: $payload);
$loggerOne->emitEvent('bar', body: 'hello world');
$loggerTwo->emitEvent(name: 'foo', severityNumber: Severity::INFO, body: $payload);

//shut down logger provider
$loggerProvider->shutdown();
