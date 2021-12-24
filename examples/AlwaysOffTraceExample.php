<?php

declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

use OpenTelemetry\API\Trace as API;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\AbstractClock;
use OpenTelemetry\SDK\Attributes;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOffSampler;
use OpenTelemetry\SDK\Trace\SamplingResult;
use OpenTelemetry\SDK\Trace\TracerProvider;

$sampler = new AlwaysOffSampler();
$samplingResult = $sampler->shouldSample(
    Context::getCurrent(),
    md5((string) microtime(true)),
    'io.opentelemetry.example',
    API\SpanKind::KIND_INTERNAL
);

if (SamplingResult::RECORD_AND_SAMPLE === $samplingResult->getDecision()) {
    $tracer = (new TracerProvider())
        ->getTracer('io.opentelemetry.contrib.php');

    // start a span, register some events
    $span = $tracer->startAndActivateSpan('session.generate');
    $span->setAttribute('remote_ip', '1.2.3.4');
    $span->setAttribute('country', 'USA');

    $timestamp = AbstractClock::getDefault()->timestamp();
    $span->addEvent('found_login', new Attributes([
        'id' => 12345,
        'username' => 'otuser',
    ]), $timestamp);
    $span->addEvent('generated_session', new Attributes([
        'id' => md5((string) microtime(true)),
    ]), $timestamp);

    $span->end(); // pass status as an optional argument
    print_r($span);  // print the span as a resulting output
} else {
    echo PHP_EOL . 'Sampling is not enabled';
}

echo PHP_EOL;
