<?php

declare(strict_types=1);

use OpenTelemetry\Config\SDK\Configuration;

require __DIR__ . '/../vendor/autoload.php';

echo 'load config SDK example starting...' . PHP_EOL;

$_SERVER['OTEL_SERVICE_NAME'] = 'opentelemetry-demo';
$_SERVER['OTEL_EXPORTER_OTLP_TRACES_ENDPOINT'] = 'http://collector:4318/v1/traces';
$_SERVER['OTEL_TRACES_SAMPLER_ARG'] = '0.5';
$_SERVER['OTEL_SDK_DISABLED'] = 'false';
$_SERVER['OTEL_EXPORTER_OTLP_TRACES_COMPRESSION'] = 'gzip';

$config = Configuration::parseFile(__DIR__ . '/load_config_env.yaml');
$sdk = $config
    ->create()
    ->setAutoShutdown(true)
    ->build();

$tracer = $sdk->getTracerProvider()->getTracer('demo');
$meter = $sdk->getMeterProvider()->getMeter('demo');
$eventLogger = $sdk->getEventLoggerProvider()->getEventLogger('demo');

$tracer->spanBuilder('root')->startSpan()->end();
$meter->createCounter('cnt')->add(1);
$eventLogger->emit('foo', 'hello, otel');

echo 'Finished!' . PHP_EOL;
