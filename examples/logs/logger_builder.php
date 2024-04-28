<?php

declare(strict_types=1);

use OpenTelemetry\API\Logs\LogRecord;
use OpenTelemetry\API\Logs\Severity;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Logs\Exporter\ConsoleExporterFactory;
use OpenTelemetry\SDK\Logs\LoggerProvider;
use OpenTelemetry\SDK\Logs\Processor\SimpleLogRecordProcessor;
use OpenTelemetry\SDK\Resource\ResourceInfo;

require __DIR__ . '/../../vendor/autoload.php';

$loggerProvider = LoggerProvider::builder()
    ->addLogRecordProcessor(
        new SimpleLogRecordProcessor(
            (new ConsoleExporterFactory())->create()
        )
    )
    ->setResource(ResourceInfo::create(Attributes::create(['foo' => 'bar'])))
    ->build();

$logger = $loggerProvider->getLogger('demo', '1.0', 'http://schema.url', ['foo' => 'bar']);

$record = (new LogRecord(['foo' => 'bar', 'baz' => 'bat', 'msg' => 'hello world']))
    ->setSeverityText('INFO')
    ->setSeverityNumber(Severity::INFO);

/**
 * Note that Loggers should only be used directly by a log appender.
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.32.0/specification/logs/bridge-api.md#logs-bridge-api
 */
$logger->emit($record);
$loggerProvider->shutdown();
