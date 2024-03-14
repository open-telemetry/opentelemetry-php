<?php

declare(strict_types=1);

use OpenTelemetry\API\Logs\EventLogger;
use OpenTelemetry\API\Logs\LogRecord;
use OpenTelemetry\Config\SDK\Configuration;

require __DIR__ . '/../vendor/autoload.php';

echo 'load config SDK example starting...' . PHP_EOL;

$config = Configuration::parseFile(__DIR__ . '/load_config.yaml');
$sdk = $config
    ->create()
    ->setAutoShutdown(true)
    ->build();

$tracer = $sdk->getTracerProvider()->getTracer('demo');
$meter = $sdk->getMeterProvider()->getMeter('demo');
$logger = $sdk->getLoggerProvider()->getLogger('demo');

$tracer->spanBuilder('root')->startSpan()->end();
$meter->createCounter('cnt')->add(1);

$eventLogger = new EventLogger($logger, 'my-domain');
$eventLogger->logEvent('foo', new LogRecord('hello, otel'));

echo 'Finished!' . PHP_EOL;
