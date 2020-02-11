<?php

declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

use OpenTelemetry\Trace\Sampler\AlwaysOffSampler;
use OpenTelemetry\Trace\TracerFactory;

$sampler = (new AlwaysOffSampler())->shouldSample();
if ($sampler) {
    $tracer = TracerFactory::getInstance()
        ->getTracer('io.opentelemetry.contrib.php');

    // start a span, register some events
    $span = $tracer->createSpan('session.generate');
    $span->setAttributes(['remote_ip' => '1.2.3.4']);
    $span->setAttribute('country', 'USA');

    $span->addEvent('found_login', [
      'id' => 12345,
      'username' => 'otuser',
    ]);
    $span->addEvent('generated_session', [
      'id' => md5(microtime(true)),
    ]);

    $span->end(); // pass status as an optional argument
    print_r($span);  // print the span as a resulting output
} else {
    echo 'Sampling is not enabled';
}
