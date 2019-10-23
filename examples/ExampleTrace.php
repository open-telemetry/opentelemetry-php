<?php

require __DIR__.'/../vendor/autoload.php';

use OpenTelemetry\Tracing\Builder;
use OpenTelemetry\Tracing\SpanContext;
use OpenTelemetry\Tracing\Sampler\AlwaysSampleSampler;

$spanContext = SpanContext::generate(); // or extract from headers
$sampler = new AlwaysSampleSampler();
$tracer = Builder::create()->setSpanContext($spanContext, $sampler)->getTracer();

// start a span, register some events
$span = $tracer->createSpan('session.generate');

// set attributes as array
$span->setAttributes([ 'remote_ip' => '5.23.99.245' ]);
// set attribute one by one
$span->setAttribute('country', 'Russia');

$span->addEvent('found_login', [
  'id' => 67235,
  'username' => 'nekufa',
]);
$span->addEvent('generated_session', [
  'id' => md5(microtime(true))
]);

$span->end(); // pass status as an optional argument
// print_r($span);  // print the span as a resulting output