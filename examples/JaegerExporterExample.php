<?php

declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

use OpenTelemetry\Sdk\Trace\AlwaysOnSampler;
use OpenTelemetry\Sdk\Trace\Attributes;
use OpenTelemetry\Sdk\Trace\JaegerExporter;
use OpenTelemetry\Sdk\Trace\SimpleSpanProcessor;
use OpenTelemetry\Sdk\Trace\TracerProvider;

$sampler = (new AlwaysOnSampler())->shouldSample(
    null,
    md5((string) (microtime(true))),
    substr(md5((string) microtime(true)), 16),
    'io.opentelemetry.jagerexporterexample'
);

$exporter = new JaegerExporter(
    'jaegerExporterExample',
    'http://jaeger:9412/api/v2/spans'
);

if ($sampler) {
    echo 'Starting JaegerExporterExample';
    $tracer = (TracerProvider::getInstance(
        [new SimpleSpanProcessor($exporter)]
    ))
        ->getTracer('io.opentelemetry.contrib.php');

    echo PHP_EOL . sprintf('Trace with id %s started ', $tracer->getActiveSpan()->getContext()->getTraceId());

    for ($i = 0; $i < 5; $i++) {
        // start a span, register some events
        $span = $tracer->startAndActivateSpan('session.generate.span' . time());
        $tracer->setActiveSpan($span);

        $span->setAttribute('remote_ip', '1.2.3.4')
            ->setAttribute('country', 'USA');

        $span->addEvent('found_login' . $i, new Attributes([
            'id' => $i,
            'username' => 'otuser' . $i,
        ]));
        $span->addEvent('generated_session', new Attributes([
            'id' => md5((string) microtime(true)),
        ]));

        $tracer->endActiveSpan();
    }
    echo PHP_EOL . 'JaegerExporterExample complete!  See the results at http://localhost:16686/';
} else {
    echo PHP_EOL . 'Sampling is not enabled';
}

echo PHP_EOL;
