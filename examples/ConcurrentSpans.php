<?php

declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

use OpenTelemetry\SDK\Trace\SpanProcessor\ConsoleSpanExporter;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;

echo 'Starting ConsoleSpanExporter' . PHP_EOL;

$tracerProvider =  new TracerProvider(
    new SimpleSpanProcessor(
        new ConsoleSpanExporter()
    )
);

$tracer = $tracerProvider->getTracer('io.opentelemetry.contrib.php');

$rootSpan = $tracer->spanBuilder('root')->startSpan();
$rootSpan->activate();

try {
    $childSpan = $tracer->spanBuilder('child-span')->startSpan();
    $childSpan->activate();
    $spans = [];
    for ($i=1; $i<=4; $i++) {
        $spans[] = $tracer->spanBuilder('http-' . $i)
            //@see https://github.com/open-telemetry/opentelemetry-collector/blob/main/model/semconv/v1.6.1/trace.go#L834
            ->setAttribute('http.method', 'GET')
            ->setAttribute('http.url', 'example.com/' . $i)
            ->setAttribute('http.status_code', 200)
            ->setAttribute('http.response_content_length', 1024)
            ->startSpan();
    }
    foreach ($spans as $span) {
        usleep((int) (0.3 * 1e6));
        $span->end();
    }
} finally {
    $childSpan->end();
}

$rootSpan->end();
