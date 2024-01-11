<?php

declare(strict_types=1);

namespace OpenTelemetry\Example;

require __DIR__ . '/../../../vendor/autoload.php';

use OpenTelemetry\SDK\Trace\TracerProviderFactory;
use OpenTelemetry\SemConv\TraceAttributes;

//@see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/configuration/sdk-environment-variables.md
putenv('OTEL_RESOURCE_ATTRIBUTES=service.version=1.0.0');
putenv('OTEL_SERVICE_NAME=example-app');
putenv('OTEL_PHP_DETECTORS=none');
putenv('OTEL_LOG_LEVEL=warning');
putenv('OTEL_TRACES_SAMPLER=parentbased,attribute,traceidratio');
putenv('OTEL_TRACES_SAMPLER_ARG=attribute.name=url.path,attribute.mode=deny,attribute.pattern=\/health$|\/test$,traceidratio.probability=1.0');
putenv('OTEL_TRACES_EXPORTER=console');

echo 'Starting attribute-based sampler example' . PHP_EOL;

$tracerProvider = (new TracerProviderFactory())->create();

$tracer = $tracerProvider->getTracer('io.opentelemetry.contrib.php');

echo 'Starting Tracer' . PHP_EOL;

$span = $tracer->spanBuilder('root')->setAttribute(TraceAttributes::URL_PATH, '/health')->startSpan();
$scope = $span->activate();

try {
    //this span will be sampled iff the root was sampled (parent-based)
    $tracer->spanBuilder('child')->startSpan()->end();
} finally {
    $scope->detach();
}
$span->end();

$tracerProvider->shutdown();
