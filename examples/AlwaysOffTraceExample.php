<?php

declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

use OpenTelemetry\Sdk\Trace\AlwaysOffSampler;
use OpenTelemetry\Sdk\Trace\Attributes;
use OpenTelemetry\Sdk\Trace\TracerProvider;

$sampler = (new AlwaysOffSampler())->shouldSample();
if ($sampler) {
    $tracer = TracerProvider::getInstance()
        ->getTracer('io.opentelemetry.contrib.php');

    // start a span, register some events
    $span = $tracer->startAndActivateSpan('session.generate');
    $span->setAttribute('remote_ip', '1.2.3.4');
    $span->setAttribute('country', 'USA');

    $span->addEvent('found_login', new Attributes([
      'id' => 12345,
      'username' => 'otuser',
    ]));
    $span->addEvent('generated_session', new Attributes([
      'id' => md5(microtime(true)),
    ]));

    $span->end(); // pass status as an optional argument
    print_r($span);  // print the span as a resulting output
} else {
    echo PHP_EOL . 'Sampling is not enabled';
}

echo PHP_EOL;
