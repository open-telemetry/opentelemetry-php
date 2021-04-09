<?php

declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

use OpenTelemetry\Context\Context;
use OpenTelemetry\Contrib\OtlpGrpc\Exporter as OTLPExporter;
use OpenTelemetry\Sdk\Trace\Attributes;
use OpenTelemetry\Sdk\Trace\Clock;
use OpenTelemetry\Sdk\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\Sdk\Trace\SamplingResult;
use OpenTelemetry\Sdk\Trace\Span;
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

$Exporter = new OTLPExporter(
    'OTLP Grpc Example Service'
);

if (SamplingResult::RECORD_AND_SAMPLE === $samplingResult->getDecision()) {
    echo 'Starting OTLPGrpcExample';
    $tracer = (new TracerProvider())
        ->addSpanProcessor(new SimpleSpanProcessor($Exporter))
        ->getTracer('io.opentelemetry.contrib.php');

    $rootSpan = $tracer->startSpan('root-span');
    // temporarily setting service name here.  It should eventually be pulled from tracer.resources.
    $rootSpan->setAttribute('service.name', 'alwaysOnOTLPGrpcExample');
    $rootSpan->setAttribute('remote_ip', '1.2.3.4')
        ->setAttribute('country', 'USA');
    $timestamp = Clock::get()->timestamp();
    $rootSpan->addEvent('found_login', $timestamp, new Attributes([
        'id' => 1,
        'username' => 'otuser',
    ]));
    $rootSpan->addEvent('generated_session', $timestamp, new Attributes([
        'id' => md5((string) microtime(true)),
    ]));
    sleep(1);

    $rootScope = Span::setCurrent($rootSpan); // set the root span active in the current context

    try {
        $span1 = $tracer->startSpan('child-span-1');
        $internalScope = Span::setCurrent($span1); // set the child span active in the context

        try {
            for ($i = 0; $i < 3; $i++) {
                $loopSpan = $tracer->startSpan('loop-' . $i);
                usleep((int) (0.5 * 1e6));
                $loopSpan->end();
            }
        } finally {
            $internalScope->close(); // deactivate child span, the rootSpan is set back as active
        }
        $span1->end();

        $span2 = $tracer->startSpan('child-span-2');
        sleep(1);
        $span2->end();
    } finally {
        $rootScope->close(); // close the scope of the root span, no active span in the context now
    }
    $rootSpan->end();

    echo PHP_EOL . 'OTLPGrpcExample complete!  ';
} else {
    echo PHP_EOL . 'OTLPGrpcExample tracing is not enabled';
}

echo PHP_EOL;
