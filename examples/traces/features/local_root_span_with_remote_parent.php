<?php

declare(strict_types=1);
/**
 * @see https://opentelemetry.io/docs/specs/semconv/messaging/messaging-spans/#consumer-spans
 */

use OpenTelemetry\API\Trace\LocalRootSpan;
use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Util\ShutdownHandler;
use OpenTelemetry\SDK\Propagation\PropagatorFactory;
use OpenTelemetry\SDK\Trace\TracerProviderFactory;

putenv('OTEL_TRACES_EXPORTER=console');
putenv('OTEL_PHP_DETECTORS=none');

require_once __DIR__ . '/../../../vendor/autoload.php';

$provider = (new TracerProviderFactory())->create();
$propagator = (new PropagatorFactory())->create();
ShutdownHandler::register([$provider, 'shutdown']);
$tracer = $provider->getTracer('example');

// “Receive” spans SHOULD be created for operations of passing messages to the application when those operations are initiated by the application code (pull-based scenarios).
$root = $tracer
    ->spanBuilder('receive')
    ->setSpanKind(SpanKind::KIND_CONSUMER)
    ->startSpan();

$rootScope = $root
    ->storeInContext(Context::getCurrent())
    ->activate();
assert(LocalRootSpan::fromContext(Context::getCurrent()) === $root);

$root->addLink(SpanContext::createFromRemoteParent('fabebb164f22d4afc51df50d9a3ff629', '87c6836d8610ac6d', 768));

// “Process” spans MAY be created in addition to “Receive” spans for pull-based scenarios for operations of processing messages.
$child = $tracer
    ->spanBuilder('process')
    ->startSpan();
$childScope = $child
    //->storeInContext($remoteContext) // preserve remote baggage etc.
    ->storeInContext(Context::getCurrent())
    ->activate();
$child->setAttribute('local_root', LocalRootSpan::current() === Span::getCurrent());

try {
    assert(LocalRootSpan::current() === $root);
    assert(LocalRootSpan::current() !== Span::getCurrent());
} finally {
    $root->end();
    $child->end();
    $childScope->detach();
    $rootScope->detach();
}
