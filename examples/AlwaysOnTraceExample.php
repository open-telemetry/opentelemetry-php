<?php

declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

use OpenTelemetry\Sdk\Trace\AlwaysOnSampler;
use OpenTelemetry\Sdk\Trace\Attributes;
use OpenTelemetry\Sdk\Trace\SamplingResult;
use OpenTelemetry\Sdk\Trace\SimpleSpanProcessor;
use OpenTelemetry\Sdk\Trace\TracerProvider;
use OpenTelemetry\Sdk\Trace\ZipkinExporter;
use OpenTelemetry\Trace as API;

$sampler = new AlwaysOnSampler();
$samplingResult = $sampler->shouldSample(
    null,
    md5((string) microtime(true)),
    substr(md5((string) microtime(true)), 16),
    'io.opentelemetry.example',
    API\SpanKind::KIND_INTERNAL
);

$zipkinExporter = new ZipkinExporter(
    'alwaysOnExporter',
    'http://zipkin:9411/api/v2/spans'
);

if (SamplingResult::RECORD_AND_SAMPLED === $samplingResult->getDecision()) {
    echo 'Starting AlwaysOnTraceExample';
    $tracer = (TracerProvider::getInstance(
        [new SimpleSpanProcessor($zipkinExporter)]
    ))
        ->getTracer('io.opentelemetry.contrib.php');

    echo PHP_EOL . sprintf('Trace with id %s started ', $tracer->getActiveSpan()->getContext()->getTraceId());

    for ($i = 0; $i < 5; $i++) {
        // start a span, register some events
        $span = $tracer->startAndActivateSpan('session.generate.span.' . time());
        $tracer->setActiveSpan($span);

        $span->setAttribute('remote_ip', '1.2.3.4')
            ->setAttribute('country', 'USA');

        $span->addEvent('found_login' . $i, new Attributes([
            'id' => $i,
            'username' => 'otuser' . $i,
        ]));
        $span->addEvent('generated_session', new Attributes([
            'id' => md5((string) microtime(true)),
        ]));

        $tracer->endActiveSpan();
    }
    echo PHP_EOL . 'AlwaysOnTraceExample complete!  See the results at http://localhost:9411/';
} else {
    echo PHP_EOL . 'Sampling is not enabled';
}

echo PHP_EOL;
