<?php

declare(strict_types=1);
require __DIR__ . '/../../../../vendor/autoload.php';

use OpenTelemetry\Context\Context;
use OpenTelemetry\Contrib\OtlpGrpc\Exporter as OTLPExporter;
use OpenTelemetry\SDK\Common\Time\ClockFactory;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;

$Exporter = new OTLPExporter();

echo 'Starting OTLPGrpc example 1';
$tracerProvider = new TracerProvider(new SimpleSpanProcessor($Exporter));
$tracer = $tracerProvider->getTracer('io.opentelemetry.contrib.php');
\OpenTelemetry\SDK\Common\Util\ShutdownHandler::register([$tracerProvider, 'shutdown']);
for ($i = 0; $i < 5; $i++) {
    // start a span, register some events
    $timestamp = ClockFactory::getDefault()->now();
    $span = $tracer->spanBuilder('session.generate.span' . microtime(true))->startSpan();
    //startAndActivateSpan('session.generate.span.' . microtime(true));

    $childSpan = $tracer
        ->spanBuilder('child')
        ->setParent($span->storeInContext(Context::getCurrent()))
        ->startSpan();

    // Temporarily setting service name here.  It should eventually be pulled from tracer.resources.
    $span->setAttribute('service.name', 'alwaysOnOTLPGrpcExample');

    $span->setAttribute('remote_ip', '1.2.3.4')
        ->setAttribute('country', 'USA');

    $span->addEvent('found_login' . $i, [
        'id' => $i,
        'username' => 'otuser' . $i,
    ], $timestamp);
    $span->addEvent('generated_session', [
        'id' => md5((string) microtime(true)),
    ], $timestamp);

    // temporarily setting service name here.  It should eventually be pulled from tracer.resources.
    $childSpan->setAttribute('service.name', 'alwaysOnOTLPGrpcExample');

    $childSpan->setAttribute('attr_one', 'one')
        ->setAttribute('attr_two', 'two');

    $childSpan->addEvent('found_event1' . $i, [
        'id' => $i,
        'username' => 'child' . $i,
    ], $timestamp);

    $childSpan->end();
    $span->end();
}
echo PHP_EOL . 'OTLPGrpc example 1 complete!  ';

echo PHP_EOL;
