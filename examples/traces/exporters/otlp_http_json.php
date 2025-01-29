<?php

declare(strict_types=1);

namespace OpenTelemetry\Example;

require __DIR__ . '/../../../vendor/autoload.php';

use OpenTelemetry\Contrib\Otlp\ContentTypes;
use OpenTelemetry\Contrib\Otlp\SpanExporter;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransportFactory;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;

$transport = (new PsrTransportFactory())->create('http://collector:4318/v1/traces', ContentTypes::JSON);
$exporter = new SpanExporter($transport);

echo 'Starting OTLP+json example';

$tracerProvider =  new TracerProvider(
    new SimpleSpanProcessor(
        $exporter
    )
);
$tracer = $tracerProvider->getTracer('io.opentelemetry.contrib.php');

$root = $span = $tracer->spanBuilder('root')->startSpan();
$root->end();
echo PHP_EOL . 'OTLP+json example complete!  ';

echo PHP_EOL;
$tracerProvider->shutdown();
