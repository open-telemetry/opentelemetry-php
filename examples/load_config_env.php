<?php

declare(strict_types=1);

use OpenTelemetry\Config\SDK\Configuration;

require __DIR__ . '/../vendor/autoload.php';

echo 'load config SDK example starting...' . PHP_EOL;

$_SERVER['OTEL_SERVICE_NAME'] = 'opentelemetry-demo';
$_SERVER['OTEL_EXPORTER_OTLP_PROTOCOL'] = 'http/protobuf';
$_SERVER['OTEL_EXPORTER_OTLP_ENDPOINT'] = 'http://collector:4318';
$_SERVER['OTEL_TRACES_SAMPLER_ARG'] = '0.5';

$config = Configuration::parseFile(__DIR__ . '/load_config_env.yaml');
$sdk = $config
    ->create()
    ->setAutoShutdown(true)
    ->build();

$tracer = $sdk->getTracerProvider()->getTracer('demo');
$meter = $sdk->getMeterProvider()->getMeter('demo');
$logger = $sdk->getLoggerProvider()->getLogger('demo');

$tracer->spanBuilder('root')->startSpan()->end();
$meter->createCounter('cnt')->add(1);
$logger->emitEvent(name: 'foo', body: 'hello, otel');

echo 'Finished!' . PHP_EOL;
