<?php

declare(strict_types=1);
require __DIR__ . '/../../../vendor/autoload.php';

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use OpenTelemetry\Contrib\Otlp\OtlpHttpTransportFactory;
use OpenTelemetry\Contrib\Otlp\Protocols;
use OpenTelemetry\Contrib\Otlp\SpanExporter;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;

Globals::registerInitializer(function (Configurator $configurator) {
    $logger = new Logger('otel-php', [new StreamHandler(STDOUT, LogLevel::DEBUG)]);

    return $configurator->withLogger($logger);
});
$transport = (new OtlpHttpTransportFactory())->withProtocol(Protocols::HTTP_JSON)->create('http://collector:4318');
$exporter = new SpanExporter($transport);

echo 'Starting OTLP+json example';

$tracerProvider =  new TracerProvider(
    new SimpleSpanProcessor(
        $exporter
    )
);
$tracer = $tracerProvider->getTracer('io.opentelemetry.contrib.php');

$root = $span = $tracer->spanBuilder('root')->startSpan();
$root->end();
echo PHP_EOL . 'OTLP+json example complete!  ';

echo PHP_EOL;
$tracerProvider->shutdown();
