<?php

declare(strict_types=1);

namespace OpenTelemetry\Example;

use OpenTelemetry\API\Logs\LogRecord;
use OpenTelemetry\API\Logs\Severity;
use OpenTelemetry\Contrib\Otlp\LogsExporter;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransportFactory;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactory;
use OpenTelemetry\SDK\Logs\LoggerProvider;
use OpenTelemetry\SDK\Logs\LogRecordLimitsBuilder;
use OpenTelemetry\SDK\Logs\Processor\SimpleLogRecordProcessor;

require __DIR__ . '/../../../vendor/autoload.php';

$transport = (new PsrTransportFactory())->create('http://collector:4318/v1/logs', 'application/json');
$exporter = new LogsExporter($transport);

$loggerProvider = new LoggerProvider(
    new SimpleLogRecordProcessor(
        $exporter
    ),
    new InstrumentationScopeFactory(
        (new LogRecordLimitsBuilder())->build()->getAttributeFactory()
    )
);
$logger = $loggerProvider->getLogger('demo', '1.0', 'https://opentelemetry.io/schemas/1.7.1', ['foo' => 'bar']);

/*$logger->emitEvent(
    name: 'foo',
    timestamp: (new \DateTime())->getTimestamp() * LogRecord::NANOS_PER_SECOND,
    severityNumber: Severity::INFO,
    body: ['foo' => 'bar', 'baz' => 'bat', 'msg' => 'hello world'],
);*/
