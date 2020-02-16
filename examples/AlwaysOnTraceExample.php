<?php

declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

use OpenTelemetry\Exporter\ZipkinExporter;
use OpenTelemetry\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\Trace\TracerFactory;

$sampler = (new AlwaysOnSampler())->shouldSample();

$zipkinExporter = new ZipkinExporter(
    'alwaysOnExporter',
    'http://zipkin:9411/api/v2/spans'
);

if ($sampler) {
    echo 'Starting AlwaysOnTraceExample';
    $tracer = (TracerFactory::getInstance(
        [new SimpleSpanProcessor($zipkinExporter)]
    ))
        ->getTracer('io.opentelemetry.contrib.php');
    for ($i = 0; $i < 5; $i++) {
        // start a span, register some events
        $span = $tracer->createSpan('session.generate.span' . $i);
        $tracer->setActiveSpan($span);

        $span->setAttributes(['remote_ip' => '1.2.3.4']);
        $span->setAttribute('country', 'USA');

        $span->addEvent('found_login' . $i, [
        'id' => $i,
        'username' => 'otuser' . $i,
    ]);
        $span->addEvent('generated_session', [
        'id' => md5((string) microtime(true)),
    ]);

        $tracer->endActiveSpan();
    }
    echo 'AlwaysOnTraceExample complete!  See the results at http://localhost:9411/';
} else {
    echo 'Sampling is not enabled';
}
