<?php

declare(strict_types=1);

use OpenTelemetry\API\Common\Time\SystemClock;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\Contrib\Otlp\SpanExporter;
use OpenTelemetry\SDK\Common\Export\Stream\StreamTransport;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanSuppression\SpanKindSuppressionStrategy\SpanKindSuppressionStrategy;
use OpenTelemetry\SDK\Trace\TracerProviderBuilder;

require_once __DIR__ . '/../../vendor/autoload.php';

$tp = (new TracerProviderBuilder())
    ->setResource(ResourceInfoFactory::emptyResource())
    ->addSpanProcessor(new BatchSpanProcessor(new SpanExporter(new StreamTransport(fopen('php://stdout', 'ab'), 'application/x-ndjson')), SystemClock::create()))
    ->setSpanSuppressionStrategy(new SpanKindSuppressionStrategy())
    ->build()
;

$t = $tp->getTracer('test');
$c1 = $t->spanBuilder('client-1')->setSpanKind(SpanKind::KIND_CLIENT)->startSpan();
$s1 = $c1->activate();

try {
    $c2 = $t->spanBuilder('client-2')->setSpanKind(SpanKind::KIND_CLIENT)->startSpan();
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
