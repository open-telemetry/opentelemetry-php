<?php

declare(strict_types=1);

use OpenTelemetry\API\Trace\LocalRootSpan;
use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\API\Trace\SpanKind;
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

//start and activate a root span
$root = $tracer
    ->spanBuilder('root')
    ->setSpanKind(SpanKind::KIND_SERVER)
    ->startSpan();
$rootScope = $root->activate();

//start and activate a child span
$child = $tracer
    ->spanBuilder('child')
    ->startSpan();
$childScope = $child->activate();

//update the name of the root span
LocalRootSpan::current()->updateName('updated')->setAttribute('my-attr', true);

//end spans and detach contexts
$child->end();
$childScope->detach();
$root->end();
$rootScope->detach();
