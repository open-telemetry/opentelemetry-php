<?php
require __DIR__ . '/../vendor/autoload.php';

use OpenTelemetry\Exporter\ZipkinExporter;
use OpenTelemetry\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\Trace\TracerFactory;
use OpenTelemetry\Trace\Sampler\AlwaysOnSampler;

$sampler = AlwaysOnSampler::shouldSample();

$zipkinExporter = new ZipkinExporter(
    'alwaysOnExporter',
    'http://zipkin:9411/api/v2/spans'
);

if ($sampler) {
    $tracer = (TracerFactory::getInstance(
        [new SimpleSpanProcessor($zipkinExporter)]
    ))
        ->getTracer('io.opentelemetry.contrib.php');
for ($i = 0; $i < 5; $i++) {
    // start a span, register some events
    $span = $tracer->createSpan('session.generate.span'. $i);
    $tracer->setActiveSpan($span);

    $span->setAttributes(['remote_ip' => '1.2.3.4']);
    $span->setAttribute('country', 'USA');

    $span->addEvent('found_login'. $i, [
        'id' => $i,
        'username' => 'otuser' . $i,
    ]);
    $span->addEvent('generated_session', [
        'id' => md5(microtime(true))
    ]);

    $tracer->endActiveSpan();
}
} else {
  echo "Sampling is not enabled";
}
