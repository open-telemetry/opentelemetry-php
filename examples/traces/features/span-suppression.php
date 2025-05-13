<?php

declare(strict_types=1);

require_once DIRNAME(__DIR__, 3) . '/vendor/autoload.php';

use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\SpanSuppression;
use OpenTelemetry\API\Trace\SpanSuppression\Strategy\SpanKindSuppressionStrategy;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\SDK\Trace\SpanExporter\ConsoleSpanExporterFactory;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;

/**
 * Span suppression is a feature used by Instrumentation Libraries to eliminate redundant child spans. For example,
 * multiple HTTP client implementations (a 3rd-party SDK client, which uses Guzzle, which uses cURL) may create a
 * span for each of the HTTP calls. This can lead to multiple nested CLIENT spans being created.
 */

SpanSuppression::setStrategies([SpanSuppression::SPAN_KIND]);

$tracerProvider = new TracerProvider(
    new SimpleSpanProcessor(
        (new ConsoleSpanExporterFactory())->create()
    ),
    resource: ResourceInfoFactory::emptyResource(),
);
$tracer = $tracerProvider->getTracer('io.opentelemetry.contrib.php');

$rootSpan = $tracer->spanBuilder('root')->setSpanKind(SpanKind::KIND_SERVER)->startSpan();
$rootScope = $rootSpan->activate();

$clientSpan = $tracer->spanBuilder('client-one')->setSpanKind(SpanKind::KIND_CLIENT)->startSpan();
$clientScope = $clientSpan->activate();
//suppress child CLIENT spans
Context::storage()->attach(
    SpanKindSuppressionStrategy::suppressSpanKind(SpanKind::KIND_CLIENT)
        ->storeInContext(Context::getCurrent())
);

//CLIENT span should be suppressed
if (!SpanSuppression::shouldSuppress(SpanKind::KIND_CLIENT)) {
    $span = $tracer->spanBuilder('client-two')->setSpanKind(SpanKind::KIND_CLIENT)->startSpan();
    $span->end();
}

$clientSpan->end();
Context::storage()->scope()?->detach(); //detach span suppression
$clientScope->detach();

$rootSpan->end();
$rootScope->detach();
