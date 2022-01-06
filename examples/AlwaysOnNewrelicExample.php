<?php

declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use OpenTelemetry\API\Trace as API;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Contrib\Newrelic\Exporter as NewrelicExporter;
use OpenTelemetry\SDK\AbstractClock;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\SamplingResult;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;

/*
 * Experimental example to send trace data to New Relic.
 * It will send PHP Otel trace data end to end across the internet to a functional backend.
 * Needs a license key to connect.  For a free account/key, go to: https://newrelic.com/signup/
 */

$sampler = new AlwaysOnSampler();
$samplingResult = $sampler->shouldSample(
    Context::getCurrent(),
    md5((string) microtime(true)),
    'io.opentelemetry.example',
    API\SpanKind::KIND_INTERNAL
);

$licenseKey = getenv('NEW_RELIC_INSERT_KEY');

// Needs a license key in the environment to connect to the backend server.

if ($licenseKey == false) {
    echo PHP_EOL . 'NEW_RELIC_INSERT_KEY not found in environment. Newrelic Example tracing is not enabled.';

    return;
}

/*
 * Default Trace API endpoint: https://trace-api.newrelic.com/trace/v1
 * EU data centers: https://trace-api.eu.newrelic.com/trace/v1
 */

$endpointUrl =  getenv('NEW_RELIC_ENDPOINT');

if ($endpointUrl == false) {
    $endpointUrl = 'https://trace-api.newrelic.com/trace/v1';
}

$newrelicExporter = new NewrelicExporter(
    'alwaysOnNewrelicExample',
    $endpointUrl,
    $licenseKey,
    new Client(),
    new HttpFactory(),
    new HttpFactory()
);

if (SamplingResult::RECORD_AND_SAMPLE === $samplingResult->getDecision()) {
    echo 'Starting AlwaysOnNewrelicExample';
    $tracer = (new TracerProvider())
        ->addSpanProcessor(new SimpleSpanProcessor($newrelicExporter))
        ->getTracer('io.opentelemetry.contrib.php');

    for ($i = 0; $i < 5; $i++) {
        // start a span, register some events
        $timestamp = AbstractClock::getDefault()->timestamp();
        $span = $tracer->startAndActivateSpan('session.generate.span.' . microtime(true));

        $spanParent = $span->getParentContext();
        echo sprintf(
            PHP_EOL . 'Exporting Trace: %s, Parent: %s, Span: %s',
            $span->getContext()->getTraceId(),
            $spanParent ? $spanParent->getSpanId() : 'None',
            $span->getContext()->getSpanId()
        );

        $span->setAttribute('remote_ip', '1.2.3.4')
            ->setAttribute('country', 'USA');

        $span->addEvent('found_login' . $i, [
            'id' => $i,
            'username' => 'otuser' . $i,
        ], $timestamp);
        $span->addEvent('generated_session', [
            'id' => md5((string) microtime(true)),
        ], $timestamp);

        $span->end();
    }
    echo PHP_EOL . 'NewrelicExample complete!  See the results at https://one.newrelic.com/launcher/distributed-tracing.launcher?pane=eyJuZXJkbGV0SWQiOiJkaXN0cmlidXRlZC10cmFjaW5nLmhvbWUiLCJzb3J0SW5kZXgiOjAsInNvcnREaXJlY3Rpb24iOiJERVNDIiwicXVlcnkiOnsib3BlcmF0b3IiOiJBTkQiLCJpbmRleFF1ZXJ5Ijp7ImNvbmRpdGlvblR5cGUiOiJJTkRFWCIsIm9wZXJhdG9yIjoiQU5EIiwiY29uZGl0aW9uU2V0cyI6W119LCJzcGFuUXVlcnkiOnsib3BlcmF0b3IiOiJBTkQiLCJjb25kaXRpb25TZXRzIjpbeyJjb25kaXRpb25UeXBlIjoiU1BBTiIsIm9wZXJhdG9yIjoiQU5EIiwiY29uZGl0aW9ucyI6W3siYXR0ciI6InNlcnZpY2UubmFtZSIsIm9wZXJhdG9yIjoiRVEiLCJ2YWx1ZSI6ImFsd2F5c09uTmV3cmVsaWNFeGFtcGxlIn1dfV19fX0=&platform[timeRange][duration]=1800000&platform[$isFallbackTimeRange]=true';
} else {
    echo PHP_EOL . 'NewrelicExample tracing is not enabled';
}

echo PHP_EOL;
