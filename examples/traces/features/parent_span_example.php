<?php

declare(strict_types=1);
require __DIR__ . '/../../../vendor/autoload.php';

use OpenTelemetry\Contrib\Jaeger\Exporter as JaegerExporter;
use OpenTelemetry\Contrib\Zipkin\Exporter as ZipkinExporter;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransportFactory;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\Span;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;

$serviceName = 'ParentSpanExample';
$sampler = new AlwaysOnSampler();

// zipkin exporter
$zipkinExporter = new ZipkinExporter(
    $serviceName,
    PsrTransportFactory::discover()->create('http://zipkin:9411/api/v2/spans')
);

// jaeger exporter
$jaegerExporter = new JaegerExporter(
    $serviceName,
    PsrTransportFactory::discover()->create('http://jaeger:9411/api/v2/spans')
);

$tracerProvider =  new TracerProvider(
    [
        new SimpleSpanProcessor($zipkinExporter),
        new SimpleSpanProcessor($jaegerExporter),
    ],
    $sampler
);

$tracer = $tracerProvider->getTracer('example.php.opentelemetry.io', '0.0.1');

$rootSpan = $tracer->spanBuilder('root-span')->startSpan();
sleep(1);

$rootScope = $rootSpan->activate(); // set the root span active in the current context

try {
    $span1 = $tracer->spanBuilder('child-span-1')->startSpan();
    $internalScope = $span1->activate(); // set the child span active in the context

    try {
        for ($i = 0; $i < 3; $i++) {
            $loopSpan = $tracer->spanBuilder('loop-' . $i)->startSpan();
            usleep((int) (0.5 * 1e6));
            $loopSpan->end();
        }
    } finally {
        $internalScope->detach(); // deactivate child span, the rootSpan is set back as active
        $span1->end();
    }

    $span2 = $tracer->spanBuilder('child-span-2')->startSpan();
    sleep(1);
    $span2->end();
} finally {
    $rootScope->detach(); // close the scope of the root span, no active span in the context now
    $rootSpan->end();
}

// start the second root span
$secondRootSpan = $tracer->spanBuilder('root-span-2')->startSpan();
sleep(2);
$secondRootSpan->end();

echo 'This example generates two traces:' . PHP_EOL;
echo '  - ' . $rootSpan->getContext()->getTraceId() . PHP_EOL;
echo '  - ' . $secondRootSpan->getContext()->getTraceId() . PHP_EOL;
echo PHP_EOL;
echo 'See the results at' . PHP_EOL;
echo 'Jaeger: http://localhost:16686/' . PHP_EOL;
echo 'Zipkin: http://localhost:9411/' . PHP_EOL;

$tracerProvider->shutdown();
