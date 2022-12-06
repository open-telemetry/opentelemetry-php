<?php

declare(strict_types=1);
require __DIR__ . '/../../../vendor/autoload.php';

use OpenTelemetry\Contrib\ZipkinToNewrelic\Exporter as ZipkinToNewrelicExporter;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransportFactory;
use OpenTelemetry\SDK\Common\Time\ClockFactory;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;

/*
 * Experimental example to send trace data to New Relic.
 * It will send PHP Otel trace data end to end across the internet to a functional backend.
 * Needs a license key to connect.  For a free account/key, go to: https://newrelic.com/signup/
 */

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

$endpointUrl = Configuration::getString('NEW_RELIC_ENDPOINT', 'https://trace-api.newrelic.com/trace/v1');
$transport = PsrTransportFactory::discover()->create($endpointUrl, $licenseKey);
$zipkinToNewrelicExporter = new ZipkinToNewrelicExporter(
    'AlwaysOnZipkinToNewrelicExample',
    $transport
);

echo 'Starting ZipkinToNewRelic example';
$tracerProvider = new TracerProvider(new SimpleSpanProcessor($zipkinToNewrelicExporter));
$tracer = $tracerProvider->getTracer('io.opentelemetry.contrib.php');

for ($i = 0; $i < 5; $i++) {
    // start a span, register some events
    $timestamp = ClockFactory::getDefault()->now();
    $span = $tracer->spanBuilder('session.generate.span.' . microtime(true))->startSpan();

    echo sprintf(
        PHP_EOL . 'Exporting Trace: %s, Span: %s',
        $span->getContext()->getTraceId(),
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
echo PHP_EOL . 'ZipkinToNewrelic example complete!  See the results at https://one.newrelic.com/launcher/distributed-tracing.launcher?pane=eyJuZXJkbGV0SWQiOiJkaXN0cmlidXRlZC10cmFjaW5nLmhvbWUiLCJzb3J0SW5kZXgiOjAsInNvcnREaXJlY3Rpb24iOiJERVNDIiwicXVlcnkiOnsib3BlcmF0b3IiOiJBTkQiLCJpbmRleFF1ZXJ5Ijp7ImNvbmRpdGlvblR5cGUiOiJJTkRFWCIsIm9wZXJhdG9yIjoiQU5EIiwiY29uZGl0aW9uU2V0cyI6W119LCJzcGFuUXVlcnkiOnsib3BlcmF0b3IiOiJBTkQiLCJjb25kaXRpb25TZXRzIjpbeyJjb25kaXRpb25UeXBlIjoiU1BBTiIsIm9wZXJhdG9yIjoiQU5EIiwiY29uZGl0aW9ucyI6W3siYXR0ciI6InNlcnZpY2UubmFtZSIsIm9wZXJhdG9yIjoiRVEiLCJ2YWx1ZSI6ImFsd2F5c09uWmlwa2luVG9OZXdyZWxpY0V4YW1wbGUifV19XX19fQ==&platform[timeRange][duration]=1800000&platform[$isFallbackTimeRange]=true';

echo PHP_EOL;
$tracerProvider->shutdown();
