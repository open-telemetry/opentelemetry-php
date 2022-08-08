<?php

declare(strict_types=1);
require __DIR__ . '/../../../../vendor/autoload.php';

use OpenTelemetry\Contrib\Jaeger\Exporter as JaegerExporter;
use OpenTelemetry\SDK\Common\Util\ShutdownHandler;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;

$exporter = JaegerExporter::fromConnectionString('http://jaeger:9412/api/v2/spans', 'AlwaysOnJaegerExample');
$tracerProvider = new TracerProvider(
    new SimpleSpanProcessor($exporter),
    new AlwaysOnSampler(),
);
ShutdownHandler::register([$tracerProvider, 'shutdown']);

echo 'Starting Jaeger example';

$tracer = $tracerProvider->getTracer('io.opentelemetry.contrib.php');

$rootSpan = $tracer->spanBuilder('root')->startSpan();
$rootSpan->activate();

for ($i = 0; $i < 5; $i++) {
    // start a span, register some events
    $span = $tracer->spanBuilder('session.generate.span-' . $i)->startSpan();

    echo sprintf(
        PHP_EOL . 'Exporting Trace: %s, Parent: %s, Span: %s',
        $span->getContext()->getTraceId(),
        $span->getParentContext()->getSpanId() ?: 'None',
        $span->getContext()->getSpanId()
    );

    $span->setAttribute('remote_ip', '1.2.3.4')
        ->setAttribute('country', 'USA');

    $span->addEvent('found_login' . $i, [
        'id' => $i,
        'username' => 'otuser' . $i,
    ]);
    $span->addEvent('generated_session', [
        'id' => md5((string) microtime(true)),
    ]);

    try {
        throw new Exception('Record exception test event');
    } catch (Exception $exception) {
        $span->recordException($exception);
    }

    $span->end();
}
$rootSpan->end();
echo PHP_EOL . 'Jaeger example complete!  See the results at http://localhost:16686/';

echo PHP_EOL;
