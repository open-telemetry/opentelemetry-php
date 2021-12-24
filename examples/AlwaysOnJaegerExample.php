<?php

declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use OpenTelemetry\API\Trace as API;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Contrib\Jaeger\Exporter as JaegerExporter;
use OpenTelemetry\SDK\AbstractClock;
use OpenTelemetry\SDK\Attributes;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\SamplingResult;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;

$sampler = new AlwaysOnSampler();
$samplingResult = $sampler->shouldSample(
    Context::getCurrent(),
    md5((string) microtime(true)),
    'io.opentelemetry.example',
    API\SpanKind::KIND_INTERNAL
);

$exporter = new JaegerExporter(
    'alwaysOnJaegerExample',
    'http://jaeger:9412/api/v2/spans',
    new Client(),
    new HttpFactory(),
    new HttpFactory()
);

if (SamplingResult::RECORD_AND_SAMPLE === $samplingResult->getDecision()) {
    echo 'Starting AlwaysOnJaegerExample';
    $tracer = (new TracerProvider())
        ->addSpanProcessor(new BatchSpanProcessor($exporter, AbstractClock::getDefault()))
        ->getTracer('io.opentelemetry.contrib.php');

    for ($i = 0; $i < 5; $i++) {
        // start a span, register some events
        $timestamp = AbstractClock::getDefault()->timestamp();
        $span = $tracer->startAndActivateSpan('session.generate.span' . microtime(true));

        $spanParent = $span->getParentContext();
        echo sprintf(
            PHP_EOL . 'Exporting Trace: %s, Parent: %s, Span: %s',
            $span->getContext()->getTraceId(),
            $spanParent ? $spanParent->getSpanId() : 'None',
            $span->getContext()->getSpanId()
        );

        $span->setAttribute('remote_ip', '1.2.3.4')
            ->setAttribute('country', 'USA');

        $span->addEvent('found_login' . $i, new Attributes([
            'id' => $i,
            'username' => 'otuser' . $i,
        ]), $timestamp);
        $span->addEvent('generated_session', new Attributes([
            'id' => md5((string) microtime(true)),
        ]), $timestamp);

        try {
            throw new Exception('Record exception test event');
        } catch (Exception $exception) {
            $span->recordException($exception);
        }

        $span->end();
    }
    echo PHP_EOL . 'AlwaysOnJaegerExample complete!  See the results at http://localhost:16686/';
} else {
    echo PHP_EOL . 'AlwaysOnJaegerExample tracing is not enabled';
}

echo PHP_EOL;
