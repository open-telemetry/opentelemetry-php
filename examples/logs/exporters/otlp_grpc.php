<?php

declare(strict_types=1);

namespace OpenTelemetry\Example;

use OpenTelemetry\API\Common\Time\Clock;
use OpenTelemetry\API\Logs\LogRecord;
use OpenTelemetry\API\Logs\Severity;
use OpenTelemetry\API\Signals;
use OpenTelemetry\Contrib\Grpc\GrpcTransportFactory;
use OpenTelemetry\Contrib\Otlp\LogsExporter;
use OpenTelemetry\Contrib\Otlp\OtlpUtil;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactory;
use OpenTelemetry\SDK\Logs\LoggerProvider;
use OpenTelemetry\SDK\Logs\LogRecordLimitsBuilder;
use OpenTelemetry\SDK\Logs\Processor\BatchLogRecordProcessor;

require __DIR__ . '/../../../vendor/autoload.php';

$transport = (new GrpcTransportFactory())->create('http://collector:4317' . OtlpUtil::method(Signals::LOGS));
$exporter = new LogsExporter($transport);
$loggerProvider = new LoggerProvider(
    new BatchLogRecordProcessor(
        $exporter,
        Clock::getDefault()
    ),
    new InstrumentationScopeFactory(
        (new LogRecordLimitsBuilder())->build()->getAttributeFactory()
    )
);
$logger = $loggerProvider->getLogger('demo', '1.0', 'http://schema.url', ['foo' => 'bar']);

$logger->emit((new LogRecord(['foo' => 'bar', 'baz' => 'bat', 'msg' => 'hello world']))->setEventName('foo')->setSeverityNumber(Severity::INFO));
$logger->emit((new LogRecord('otel is great'))->setEventName('bar'));

$loggerProvider->shutdown();
