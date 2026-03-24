<?php

declare(strict_types=1);

namespace OpenTelemetry\Example;

use OpenTelemetry\API\Common\Time\Clock;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Contrib\Otlp\SpanExporter;
use OpenTelemetry\SDK\Common\Export\Stream\StreamTransportFactory;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanSuppression\ManualSuppressionStrategy\ManualSuppressionContextKey;
use OpenTelemetry\SDK\Trace\SpanSuppression\ManualSuppressionStrategy\ManualSuppressionStrategy;
use OpenTelemetry\SDK\Trace\TracerProviderBuilder;

require_once __DIR__ . '/../../vendor/autoload.php';

$tp = (new TracerProviderBuilder())
    ->setResource(ResourceInfoFactory::emptyResource())
    ->addSpanProcessor(new BatchSpanProcessor(new SpanExporter((new StreamTransportFactory())->create('php://stdout', 'application/x-ndjson')), Clock::getDefault()))
    ->setSpanSuppressionStrategy(new ManualSuppressionStrategy())
    ->build()
;

$t = $tp->getTracer('test');
$c1 = $t->spanBuilder('client-1')->startSpan();
$s1 = $c1->activate();

try {
    // Manual suppress client-2 span by activating a context with the ManualSuppressionContextKey::Suppress key set to true.
    $manualSuppressScope = Context::getCurrent()->with(ManualSuppressionContextKey::Suppress, true)->activate();
    $c2 = $t->spanBuilder('client-2')->startSpan();
    $s2 = $c2->activate();

    try {
        // ...
    } finally {
        $s2->detach();
        $c2->end();
        $manualSuppressScope->detach();
    }

    $c3 = $t->spanBuilder('client-3')->startSpan();
    $s3 = $c3->activate();

    try {
        // ...
    } finally {
        $s3->detach();
        $c3->end();
    }
} finally {
    $s1->detach();
    $c1->end();
}

$tp->shutdown();
