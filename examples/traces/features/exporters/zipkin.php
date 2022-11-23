<?php

declare(strict_types=1);
require __DIR__ . '/../../../../vendor/autoload.php';

use OpenTelemetry\Contrib\Zipkin\Exporter as ZipkinExporter;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransportFactory;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;

$transport = PsrTransportFactory::discover()->create('http://zipkin:9411/api/v2/spans', 'application/json');
$zipkinExporter = new ZipkinExporter(
    'alwaysOnZipkinExample',
    $transport
);
$tracerProvider =  new TracerProvider(
    new SimpleSpanProcessor(
        $zipkinExporter
    )
);
$tracer = $tracerProvider->getTracer('io.opentelemetry.contrib.php');

echo 'Starting Zipkin example';

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
echo PHP_EOL . 'Zipkin example complete!  See the results at http://localhost:9411/';

echo PHP_EOL;
$tracerProvider->shutdown();
