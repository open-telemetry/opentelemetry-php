<?php

declare(strict_types=1);
require __DIR__ . '/../../../vendor/autoload.php';

use OpenTelemetry\SDK\Common\Util\ShutdownHandler;
use OpenTelemetry\SDK\Trace\TracerProviderFactory;

//@see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/sdk-environment-variables.md
putenv('OTEL_RESOURCE_ATTRIBUTES=service.version=1.0.0');
putenv('OTEL_SERVICE_NAME=example-app');
putenv('OTEL_LOG_LEVEL=warning');
putenv('OTEL_TRACES_SAMPLER=traceidratio');
putenv('OTEL_TRACES_SAMPLER_ARG=0.95');
putenv('OTEL_TRACES_EXPORTER=console');
putenv('OTEL_PHP_TRACES_PROCESSOR=batch');
putenv('OTEL_BSP_SCHEDULE_DELAY=10000');

echo 'Creating Exporter From Environment' . PHP_EOL;

$tracerProvider = (new TracerProviderFactory('example'))->create();
ShutdownHandler::register([$tracerProvider, 'shutdown']);

$tracer = $tracerProvider->getTracer('io.opentelemetry.contrib.php');

echo 'Starting Tracer' . PHP_EOL;

$rootSpan = $tracer->spanBuilder('root')->startSpan();
$scope = $rootSpan->activate();
$rootSpan->addEvent('my_event')->setAttribute('fruit', 'apple');
$rootSpan->end();
$scope->detach();
