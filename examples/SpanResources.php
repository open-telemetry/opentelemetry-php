<?php

declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

use OpenTelemetry\SDK\Resource\ResourceConstants;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Trace\Attributes;
use OpenTelemetry\SDK\Trace\SpanProcessor\ConsoleSpanExporter;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;

echo 'Starting ConsoleSpanExporter' . PHP_EOL;

$resource = ResourceInfo::create(new Attributes([
    //@see https://github.com/open-telemetry/opentelemetry-specification/tree/main/specification/resource/semantic_conventions
    ResourceConstants::SERVICE_NAMESPACE => 'foo',
    ResourceConstants::SERVICE_NAME => 'bar',
    ResourceConstants::SERVICE_INSTANCE_ID => 1,
    ResourceConstants::SERVICE_VERSION => '0.1',
    ResourceConstants::HOST_HOSTNAME => \gethostname(),
    ResourceConstants::DEPLOYMENT_ENVIRONMENT => 'development',
    ResourceConstants::HOST_ARCH => strtolower(php_uname('m')),
    ResourceConstants::HOST_TYPE => strtolower(php_uname('s')),
]));

$tracerProvider =  new TracerProvider(
    new SimpleSpanProcessor(
        new ConsoleSpanExporter()
    ),
    null,
    $resource
);

$tracer = $tracerProvider->getTracer('io.opentelemetry.contrib.php');

$rootSpan = $tracer->spanBuilder('root')->startSpan();
$rootSpan->activate();

$rootSpan->end();
