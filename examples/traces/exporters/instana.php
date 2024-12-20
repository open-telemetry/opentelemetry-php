<?php

declare(strict_types=1);

namespace OpenTelemetry\Example;

require __DIR__ . '/../../../vendor/autoload.php';

use OpenTelemetry\Contrib\Instana\SpanExporter as InstanaExporter;
use OpenTelemetry\Contrib\Instana\SpanExporterFactory as InstanaSpanExporterFactory;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;

$tracerProvider = new TracerProvider(
    new SimpleSpanProcessor(
        (new InstanaSpanExporterFactory)->create()
    )
);

$tracer = $tracerProvider->getTracer('io.opentelemetry.contrib.php');

echo 'Starting Instana example';

$root = $span = $tracer->spanBuilder('root')->startSpan();
$scope = $span->activate();

for ($i = 0; $i < 3; $i++) {
    // start a span, register some events
    $span = $tracer->spanBuilder('loop-' . $i)->startSpan();

    $span->setAttribute('remote_ip', '1.2.3.4')
        ->setAttribute('country', 'USA');

    $span->addEvent('found_login' . $i, [
        'id' => $i,
        'username' => 'otuser' . $i,
    ]);
    $span->addEvent('generated_session', [
        'id' => md5((string) microtime(true)),
    ]);

    $span->end();
}
$scope->detach();
$root->end();
echo PHP_EOL . 'Instana example complete!';

echo PHP_EOL;
$tracerProvider->shutdown();
