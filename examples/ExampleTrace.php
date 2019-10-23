<?php

require __DIR__.'/../vendor/autoload.php';

use OpenTelemetry\Tracing\Builder;
use OpenTelemetry\Tracing\SpanContext;
use OpenTelemetry\Tracing\Sampler\AlwaysSampleSampler;
use OpenTelemetry\Tracing\Sampler\NeverSampleSampler;
//use OpenTelemetry\Tracing\Sampler\QpsSampler;
//$sampler = QpsSampler::shouldSample();

$sampler = AlwaysSampleSampler::shouldSample();
//$sampler = NeverSampleSampler::shouldSample();
// echo $sampler ? 'true' : 'false';

// If the sampler is enabled, we should start our sampling.
if($sampler) {
    $spanContext = SpanContext::generate(); // or extract from headers
    $tracer = Builder::create()->setSpanContext($spanContext)->getTracer();
    
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
    print_r($span);  // print the span as a resulting output
} else {
    echo "Sampling is not enabled";
}