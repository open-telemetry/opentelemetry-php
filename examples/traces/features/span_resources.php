<?php

declare(strict_types=1);

namespace OpenTelemetry\Example;

require __DIR__ . '/../../../vendor/autoload.php';

use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\SDK\Trace\SpanExporter\ConsoleSpanExporterFactory;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;
use OpenTelemetry\SemConv\ResourceAttributes;

echo 'Starting ConsoleSpanExporter' . PHP_EOL;

$resource = ResourceInfoFactory::defaultResource()->merge(ResourceInfo::create(Attributes::create([
    //@see https://github.com/open-telemetry/opentelemetry-specification/tree/main/specification/resource/semantic_conventions
    ResourceAttributes::SERVICE_NAMESPACE => 'foo',
    ResourceAttributes::SERVICE_NAME => 'bar',
    ResourceAttributes::SERVICE_INSTANCE_ID => 1,
    ResourceAttributes::SERVICE_VERSION => '0.1',
    ResourceAttributes::DEPLOYMENT_ENVIRONMENT => 'development',
])));

$tracerProvider =  new TracerProvider(
    new SimpleSpanProcessor(
        (new ConsoleSpanExporterFactory())->create()
    ),
    null,
    $resource
);

$tracer = $tracerProvider->getTracer('io.opentelemetry.contrib.php');

$span = $tracer->spanBuilder('root')->startSpan();
$span->end();

$tracerProvider->shutdown();
