<?php

declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

use OpenTelemetry\Context\Context;
use OpenTelemetry\Contrib\OtlpGrpc\Exporter as OTLPExporter;
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

$Exporter = new OTLPExporter();

if (SamplingResult::RECORD_AND_SAMPLE === $samplingResult->getDecision()) {
    echo 'Starting OTLPGrpcExample';
    $tracer = (new TracerProvider())
        ->addSpanProcessor(new SimpleSpanProcessor($Exporter))
        ->getTracer('io.opentelemetry.contrib.php');

    for ($i = 0; $i < 5; $i++) {
        // start a span, register some events
        $timestamp = Clock::get()->timestamp();
        $span = $tracer->startAndActivateSpan('session.generate.span' . microtime(true));
        //startAndActivateSpan('session.generate.span.' . microtime(true));

        $childSpan = $tracer->startSpan('child');

        // Temporarily setting service name here.  It should eventually be pulled from tracer.resources.
        $span->setAttribute('service.name', 'alwaysOnOTLPGrpcExample');

        $span->setAttribute('remote_ip', '1.2.3.4')
            ->setAttribute('country', 'USA');

        $span->addEvent('found_login' . $i, $timestamp, new Attributes([
            'id' => $i,
            'username' => 'otuser' . $i,
        ]));
        $span->addEvent('generated_session', $timestamp, new Attributes([
            'id' => md5((string) microtime(true)),
        ]));

        // temporarily setting service name here.  It should eventually be pulled from tracer.resources.
        $childSpan->setAttribute('service.name', 'alwaysOnOTLPGrpcExample');

        $childSpan->setAttribute('attr_one', 'one')
            ->setAttribute('attr_two', 'two');

        $childSpan->addEvent('found_event1' . $i, $timestamp, new Attributes([
            'id' => $i,
            'username' => 'child' . $i,
        ]));

        $childSpan->end();
        $span->end();
    }
    echo PHP_EOL . 'OTLPGrpcExample complete!  ';
} else {
    echo PHP_EOL . 'OTLPGrpcExample tracing is not enabled';
}

echo PHP_EOL;
