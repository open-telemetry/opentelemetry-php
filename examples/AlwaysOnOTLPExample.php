<?php

declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

use OpenTelemetry\Contrib\otlp\Exporter as otlpExporter;
use OpenTelemetry\Sdk\Trace\Attributes;
use OpenTelemetry\Sdk\Trace\Clock;
use OpenTelemetry\Sdk\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\Sdk\Trace\SamplingResult;
use OpenTelemetry\Sdk\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\Sdk\Trace\TracerProvider;
use OpenTelemetry\Trace as API;

$sampler = new AlwaysOnSampler();
$samplingResult = $sampler->shouldSample(
    null,
    md5((string) microtime(true)),
    substr(md5((string) microtime(true)), 16),
    'io.opentelemetry.example',
    API\SpanKind::KIND_INTERNAL
);

$otlpExporter = new otlpExporter();

if (SamplingResult::RECORD_AND_SAMPLED === $samplingResult->getDecision()) {
    echo 'Starting OTLP TraceExample';
    $tracer = (new TracerProvider())
        ->addSpanProcessor(new SimpleSpanProcessor($otlpExporter))
        ->getTracer('io.opentelemetry.contrib.php');

    echo PHP_EOL . sprintf('Trace with id %s started ', $tracer->getActiveSpan()->getContext()->getTraceId());

    for ($i = 0; $i < 5; $i++) {
        // start a span, register some events
        $timestamp = Clock::get()->timestamp();
        $span = $tracer->startAndActivateSpan('session.generate.span.' . time());
        $tracer->setActiveSpan($span);

        $span->setAttribute('remote_ip', '1.2.3.4')
            ->setAttribute('country', 'USA');

        $span->addEvent('found_login' . $i, $timestamp, new Attributes([
            'id' => $i,
            'username' => 'otuser' . $i,
        ]));
        $span->addEvent('generated_session', $timestamp, new Attributes([
            'id' => md5((string) microtime(true)),
        ]));

        $tracer->endActiveSpan();
    }
    echo PHP_EOL . 'OTLP TraceExample complete!  See the results at http://localhost:9411/';
} else {
    echo PHP_EOL . 'Sampling is not enabled';
}

echo PHP_EOL;
