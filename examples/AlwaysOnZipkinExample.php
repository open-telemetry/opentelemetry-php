<?php

declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

use OpenTelemetry\Context\Context;
use OpenTelemetry\Contrib\Zipkin\Exporter as ZipkinExporter;
use OpenTelemetry\Sdk\Trace\Attributes;
use OpenTelemetry\Sdk\Trace\Clock;
use OpenTelemetry\Sdk\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\Sdk\Trace\SamplingResult;
use OpenTelemetry\Sdk\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\Sdk\Trace\TracerProvider;
use OpenTelemetry\Trace as API;

$sampler = new AlwaysOnSampler();
$samplingResult = $sampler->shouldSample(
    Context::getCurrent(),
    md5((string) microtime(true)),
    'io.opentelemetry.example',
    API\SpanKind::KIND_INTERNAL
);

$zipkinExporter = new ZipkinExporter(
    'alwaysOnZipkinExample',
    'http://zipkin:9411/api/v2/spans'
);

if (SamplingResult::RECORD_AND_SAMPLE === $samplingResult->getDecision()) {
    echo 'Starting AlwaysOnZipkinExample';
    $tracer = (new TracerProvider())
        ->addSpanProcessor(new SimpleSpanProcessor($zipkinExporter))
        ->getTracer('io.opentelemetry.contrib.php');

    for ($i = 0; $i < 5; $i++) {
        // start a span, register some events
        $timestamp = Clock::get()->timestamp();
        $span = $tracer->startAndActivateSpan('session.generate.span.' . microtime(true));

        $spanParent = $span->getParent();
        echo sprintf(
            PHP_EOL . 'Exporting Trace: %s, Parent: %s, Span: %s',
            $span->getContext()->getTraceId(),
            $spanParent ? $spanParent->getSpanId() : 'None',
            $span->getContext()->getSpanId()
        );

        $span->setAttribute('remote_ip', '1.2.3.4')
            ->setAttribute('country', 'USA');

        $span->addEvent('found_login' . $i, $timestamp, new Attributes([
            'id' => $i,
            'username' => 'otuser' . $i,
        ]));
        $span->addEvent('generated_session', $timestamp, new Attributes([
            'id' => md5((string) microtime(true)),
        ]));

        try {
            throw new Exception('Record exception test event');
        } catch (Exception $exception) {
            $span->recordException($exception);
        }

        $tracer->endActiveSpan();
    }
    echo PHP_EOL . 'AlwaysOnZipkinExample complete!  See the results at http://localhost:9411/';
} else {
    echo PHP_EOL . 'AlwaysOnZipkinExample tracing is not enabled';
}

echo PHP_EOL;
