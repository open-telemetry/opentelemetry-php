<?php

declare(strict_types=1);

use OpenTelemetry\API\Common\Time\SystemClock;
use OpenTelemetry\API\Instrumentation\SpanSuppression\SemanticConventionSuppressionStrategy\SemanticConventionSuppressionStrategy;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\Contrib\Otlp\SpanExporter;
use OpenTelemetry\SDK\Common\Export\Stream\StreamTransport;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProviderBuilder;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/SemanticConventionResolver.php';

// Two nested http client instrumentations

$tp = (new TracerProviderBuilder())
    ->setResource(ResourceInfoFactory::emptyResource())
    ->addSpanProcessor(new BatchSpanProcessor(new SpanExporter(new StreamTransport(fopen('php://stdout', 'ab'), 'application/x-ndjson')), SystemClock::create()))
    ->setSpanSuppressionStrategy(new SemanticConventionSuppressionStrategy([
        new SemanticConventionResolver(),
    ]))
    ->build()
;

$t = $tp->getTracer('test');
$c1 = $tp
    ->getTracer('instrumentation-1', schemaUrl: 'https://opentelemetry.io/schemas/1.33.0')
    ->spanBuilder('GET')
    ->setSpanKind(SpanKind::KIND_CLIENT)
    ->setAttributes([
        'http.request.method' => 'GET',
        'server.address' => 'http://example.com',
        'server.port' => '80',
        'url.full' => 'http://example.com',
    ])
    ->startSpan();
$s1 = $c1->activate();

try {
    $c2 = $tp
        ->getTracer('instrumentation-2', schemaUrl: 'https://opentelemetry.io/schemas/1.31.0')
        ->spanBuilder('GET')
        ->setSpanKind(SpanKind::KIND_CLIENT)
        ->setAttributes([
            'http.request.method' => 'GET',
            'server.address' => 'http://example.com',
            'server.port' => '80',
            'url.full' => 'http://example.com',
        ])
        ->startSpan();
    $s2 = $c2->activate();

    try {
        // ...
    } finally {
        $s2->detach();
        $c2->end();
    }
} finally {
    $s1->detach();
    $c1->end();
}

$tp->shutdown();
