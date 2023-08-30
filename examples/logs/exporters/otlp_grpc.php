<?php

declare(strict_types=1);

namespace OpenTelemetry\Example;

use OpenTelemetry\API\Logs\EventLogger;
use OpenTelemetry\API\Logs\LogRecord;
use OpenTelemetry\API\Signals;
use OpenTelemetry\Contrib\Grpc\GrpcTransportFactory;
use OpenTelemetry\Contrib\Otlp\LogsExporter;
use OpenTelemetry\Contrib\Otlp\OtlpUtil;
use Opentelemetry\Proto\Logs\V1\SeverityNumber;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactory;
use OpenTelemetry\SDK\Common\Time\ClockFactory;
use OpenTelemetry\SDK\Logs\LoggerProvider;
use OpenTelemetry\SDK\Logs\LogRecordLimitsBuilder;
use OpenTelemetry\SDK\Logs\Processor\BatchLogRecordProcessor;

require __DIR__ . '/../../../vendor/autoload.php';

$transport = (new GrpcTransportFactory())->create('http://collector:4317' . OtlpUtil::method(Signals::LOGS));
$exporter = new LogsExporter($transport);
$loggerProvider = new LoggerProvider(
    new BatchLogRecordProcessor(
        $exporter,
        ClockFactory::getDefault()
    ),
    new InstrumentationScopeFactory(
        (new LogRecordLimitsBuilder())->build()->getAttributeFactory()
    )
);
$logger = $loggerProvider->getLogger('demo', '1.0', 'http://schema.url', ['foo' => 'bar']);
$eventLogger = new EventLogger($logger, 'my-domain');

$eventLogger->logEvent(
    'foo',
    (new LogRecord(['foo' => 'bar', 'baz' => 'bat', 'msg' => 'hello world']))
        ->setSeverityText('INFO')
        ->setSeverityNumber(SeverityNumber::SEVERITY_NUMBER_INFO)
);

$eventLogger->logEvent(
    'foo',
    new LogRecord('otel is great')
);

$loggerProvider->shutdown();
