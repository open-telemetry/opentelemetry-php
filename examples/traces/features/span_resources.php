<?php

declare(strict_types=1);
require __DIR__ . '/../../../vendor/autoload.php';

use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Util\ShutdownHandler;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\SDK\Trace\SpanExporter\ConsoleSpanExporter;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;
use OpenTelemetry\SemConv\ResourceAttributes;

echo 'Starting ConsoleSpanExporter' . PHP_EOL;

$resource = ResourceInfoFactory::merge(ResourceInfo::create(Attributes::create([
    //@see https://github.com/open-telemetry/opentelemetry-specification/tree/main/specification/resource/semantic_conventions
    ResourceAttributes::SERVICE_NAMESPACE => 'foo',
    ResourceAttributes::SERVICE_NAME => 'bar',
    ResourceAttributes::SERVICE_INSTANCE_ID => 1,
    ResourceAttributes::SERVICE_VERSION => '0.1',
    ResourceAttributes::DEPLOYMENT_ENVIRONMENT => 'development',
])), ResourceInfoFactory::defaultResource());

$tracerProvider =  new TracerProvider(
    new SimpleSpanProcessor(
        new ConsoleSpanExporter()
    ),
    null,
    $resource
);
ShutdownHandler::register([$tracerProvider, 'shutdown']);

$tracer = $tracerProvider->getTracer('io.opentelemetry.contrib.php');

$span = $tracer->spanBuilder('root')->startSpan();
$span->end();
