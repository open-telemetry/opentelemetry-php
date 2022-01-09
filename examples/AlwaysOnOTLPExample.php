<?php

declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use OpenTelemetry\Contrib\OtlpHttp\Exporter as OTLPExporter;
use OpenTelemetry\SDK\Attributes;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;

putenv('OTEL_EXPORTER_OTLP_ENDPOINT=http://collector:4318/v1/traces');
$exporter = new OTLPExporter(
    new Client(),
    new HttpFactory(),
    new HttpFactory()
);

echo 'Starting OTLPExample';

$tracerProvider =  new TracerProvider(
    new SimpleSpanProcessor(
        $exporter
    )
);
$tracer = $tracerProvider->getTracer();

$root = $span = $tracer->spanBuilder('root')->startSpan();
$span->activate();

for ($i = 0; $i < 3; $i++) {
    // start a span, register some events
    $span = $tracer->spanBuilder('loop-' . $i)->startSpan();

    $span->setAttribute('remote_ip', '1.2.3.4')
        ->setAttribute('country', 'USA');

    $span->addEvent('found_login' . $i, new Attributes([
        'id' => $i,
        'username' => 'otuser' . $i,
    ]));
    $span->addEvent('generated_session', new Attributes([
        'id' => md5((string) microtime(true)),
    ]));

    $span->end();
}
$root->end();
echo PHP_EOL . 'OTLPExample complete!  ';

echo PHP_EOL;
