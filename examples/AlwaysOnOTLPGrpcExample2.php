<?php

declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

use OpenTelemetry\API\Trace as API;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Contrib\OtlpGrpc\Exporter as OTLPExporter;
use OpenTelemetry\SDK\AbstractClock;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\SamplingResult;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;

$sampler = new AlwaysOnSampler();
$samplingResult = $sampler->shouldSample(
    Context::getCurrent(),
    md5((string) microtime(true)),
    'io.opentelemetry.example',
    API\SpanKind::KIND_SERVER
);

$Exporter = new OTLPExporter();

if (SamplingResult::RECORD_AND_SAMPLE === $samplingResult->getDecision()) {
    echo 'Starting OTLPGrpcExample';
    $tracer = (new TracerProvider())
        ->addSpanProcessor(new SimpleSpanProcessor($Exporter))
        ->getTracer('io.opentelemetry.contrib.php');
    $rootSpan = $tracer->startSpan('root-span', null, API\SpanKind::KIND_SERVER);

    // temporarily setting service name here.  It should eventually be pulled from tracer.resources.
    $rootSpan->setAttribute('service.name', 'alwaysOnOTLPGrpcExample');

    $rootSpan->setAttribute('remote_ip', '1.2.3.4')
        ->setAttribute('country', 'USA');
    $timestamp = AbstractClock::getDefault()->timestamp();
    $rootSpan->addEvent('found_login', new Attributes([
        'id' => 1,
        'username' => 'otuser',
    ]), $timestamp);
    $rootSpan->addEvent('generated_session', new Attributes([
        'id' => md5((string) microtime(true)),
    ]), $timestamp);
    sleep(1);

    $rootScope = $rootSpan->activate(); // set the root span active in the current context

    try {
        $span1 = $tracer->startSpan('child-span-1', null, API\SpanKind::KIND_SERVER);
        $internalScope = $span1->activate(); // set the child span active in the context

        try {
            for ($i = 0; $i < 3; $i++) {
                $loopSpan = $tracer->startSpan('loop-' . $i, null, API\SpanKind::KIND_CLIENT);
                $loopSpan->setAttribute('db.statement', 'select foo from bar');
                $loopSpan->setAttribute('db.system', 'mysql');
                $loopSpan->setAttribute('db.query', 'select foo from bar');
                usleep((int) (0.5 * 1e6));
                $loopSpan->end();
            }
        } finally {
            $internalScope->close(); // deactivate child span, the rootSpan is set back as active
        }
        $span1->end();

        $span2 = $tracer->startSpan('child-span-2', null, API\SpanKind::KIND_SERVER);
        $span2->setAttribute('error.message', 'this is an error');
        $span2->setAttribute('error.class', 'error.class.this.is');
        sleep(1);
        $internalScope = $span2->activate(); // set the child span active in the context

        try {
            $internalSpan = $tracer->startSpan('internal', null, API\SpanKind::KIND_CLIENT);
            usleep((int) (0.5 * 1e6));
            $internalSpan->end();

            $internalSpan = $tracer->startSpan('external', null, API\SpanKind::KIND_CLIENT);
            usleep((int) (0.5 * 1e6));
            $internalSpan->setAttribute('http.method', 'GET');
            $internalSpan->end();
        } finally {
            $internalScope->close(); // deactivate child span, the rootSpan is set back as active
        }
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
