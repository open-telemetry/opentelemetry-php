<?php

declare(strict_types=1);

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use OpenTelemetry\API\Common\Log\LoggerHolder;
use OpenTelemetry\API\Common\Signal\Signals;
use OpenTelemetry\API\Logs\EventLogger;
use OpenTelemetry\API\Logs\LogRecord;
use OpenTelemetry\Contrib\Grpc\GrpcTransportFactory;
use OpenTelemetry\Contrib\Otlp\LogsExporter;
use OpenTelemetry\Contrib\Otlp\OtlpUtil;
use Opentelemetry\Proto\Logs\V1\SeverityNumber;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactory;
use OpenTelemetry\SDK\Common\Time\ClockFactory;
use OpenTelemetry\SDK\Logs\LoggerProvider;
use OpenTelemetry\SDK\Logs\LogRecordLimitsBuilder;
use OpenTelemetry\SDK\Logs\Processor\BatchLogsProcessor;
use Psr\Log\LogLevel;

require __DIR__ . '/../../../vendor/autoload.php';

LoggerHolder::set(
    new Logger('otel-php', [new StreamHandler(STDOUT, LogLevel::DEBUG)])
);

$transport = (new GrpcTransportFactory())->create('http://collector:4317' . OtlpUtil::method(Signals::LOGS));
$exporter = new LogsExporter($transport);

$loggerProvider = new LoggerProvider(
    new BatchLogsProcessor(
        $exporter,
        ClockFactory::getDefault()
    ),
    new InstrumentationScopeFactory(
        (new LogRecordLimitsBuilder())->build()->getAttributeFactory()
    )
);
$logger = $loggerProvider->getLogger('demo', '1.0', 'http://schema.url', true, ['foo' => 'bar']);
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
