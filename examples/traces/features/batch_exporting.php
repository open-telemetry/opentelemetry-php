<?php

declare(strict_types=1);

namespace OpenTelemetry\Example;

require __DIR__ . '/../../../vendor/autoload.php';

use OpenTelemetry\SDK\Common\Time\ClockFactory;
use OpenTelemetry\SDK\Trace\SpanExporter\ConsoleSpanExporterFactory;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;

$delayMillis = 3000;

echo 'Starting ConsoleSpanExporter with BatchSpanProcessor' . PHP_EOL;
echo sprintf('Sending batches every %dms and on shutdown', $delayMillis) . PHP_EOL;

$tracerProvider = new TracerProvider(
    new BatchSpanProcessor(
        (new ConsoleSpanExporterFactory())->create(),
        ClockFactory::getDefault(),
        2048, //max spans to queue before sending to exporter
        $delayMillis, //batch delay milliseconds
    )
);

$tracer = $tracerProvider->getTracer('io.opentelemetry.contrib.php');

$rootSpan = $tracer->spanBuilder('root')->startSpan();
$scope = $rootSpan->activate();

//3 spans should be sent at 3 seconds
for ($i = 1; $i <= 4; $i++) {
    $span = $tracer->spanBuilder('span-' . $i)->startSpan();
    sleep(1);
    $span->end();
}
//4th span and root span should be sent on shutdown
$rootSpan->end();
$scope->detach();
sleep(1);
$tracerProvider->shutdown();
