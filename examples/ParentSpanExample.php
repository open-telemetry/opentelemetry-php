<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use OpenTelemetry\Contrib\Jaeger\Exporter as JaegerExporter;
use OpenTelemetry\Contrib\Zipkin\Exporter as ZipkinExporter;
use OpenTelemetry\Sdk\Resource\ResourceInfo;
use OpenTelemetry\Sdk\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\Sdk\Trace\Span;
use OpenTelemetry\Sdk\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\Sdk\Trace\TracerProvider;

$serviceName = 'ParentSpanExample';
$resource = ResourceInfo::defaultResource();
$sampler = new AlwaysOnSampler();
$tracerProvider = new TracerProvider($resource, $sampler);

// zipkin exporter
$zipkinExporter = new ZipkinExporter(
    $serviceName,
    'http://zipkin:9411/api/v2/spans',
    new Client(),
    new HttpFactory(),
    new HttpFactory()
);
$tracerProvider->addSpanProcessor(new SimpleSpanProcessor($zipkinExporter));

// jaeger exporter
$jaegerExporter = new JaegerExporter(
    $serviceName,
    'http://jaeger:9412/api/v2/spans',
    new Client(),
    new HttpFactory(),
    new HttpFactory()
);
$tracerProvider->addSpanProcessor(new SimpleSpanProcessor($jaegerExporter));

$tracer = $tracerProvider->getTracer('example.php.opentelemetry.io', '0.0.1');

$rootSpan = $tracer->startSpan('root-span');
sleep(1);

$rootScope = $rootSpan->activate(); // set the root span active in the current context

try {
    $span1 = $tracer->startSpan('child-span-1');
    $internalScope = $span1->activate(); // set the child span active in the context

    try {
        for ($i = 0; $i < 3; $i++) {
            $loopSpan = $tracer->startSpan('loop-' . $i);
            usleep((int) (0.5 * 1e6));
            $loopSpan->end();
        }
    } finally {
        $internalScope->close(); // deactivate child span, the rootSpan is set back as active
    }
    $span1->end();

    $span2 = $tracer->startSpan('child-span-2');
    sleep(1);
    $span2->end();
} finally {
    $rootScope->close(); // close the scope of the root span, no active span in the context now
}
$rootSpan->end();

// start the second root span
$secondRootSpan = $tracer->startSpan('root-span-2');
sleep(2);
$secondRootSpan->end();

echo 'This example generates two traces:' . PHP_EOL;
echo '  - ' . $rootSpan->getContext()->getTraceId() . PHP_EOL;
echo '  - ' . $secondRootSpan->getContext()->getTraceId() . PHP_EOL;
echo PHP_EOL;
echo 'See the results at' . PHP_EOL;
echo 'Jaeger: http://localhost:16686/' . PHP_EOL;
echo 'Zipkin: http://localhost:9411/' . PHP_EOL;
