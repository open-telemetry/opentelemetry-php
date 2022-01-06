<?php

declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use OpenTelemetry\Contrib\Jaeger\Exporter as JaegerExporter;
use OpenTelemetry\SDK\Attributes;
use OpenTelemetry\SDK\GlobalLoggerHolder;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;
use Psr\Log\LogLevel;

$logger = new Logger('otel-php', [new StreamHandler(STDOUT, LogLevel::DEBUG)]);
GlobalLoggerHolder::set($logger);

$exporter = JaegerExporter::fromConnectionString('http://jaeger:9412/api/v2/spans', 'AlwaysOnJaegerExample');
$tracerProvider = new TracerProvider(
    new SimpleSpanProcessor($exporter),
    new AlwaysOnSampler(),
);

echo 'Starting AlwaysOnJaegerExample';

$tracer = $tracerProvider->getTracer();

$rootSpan = $tracer->spanBuilder('root')->startSpan();
$rootSpan->activate();

for ($i = 0; $i < 5; $i++) {
    // start a span, register some events
    $span = $tracer->spanBuilder('session.generate.span-' . $i)->startSpan();

    echo sprintf(
        PHP_EOL . 'Exporting Trace: %s, Parent: %s, Span: %s',
        $span->getContext()->getTraceId(),
        $span->getParentContext()->getSpanId() ?: 'None',
        //$spanParent ? $spanParent->getSpanId() : 'None',
        $span->getContext()->getSpanId()
    );

    $span->setAttribute('remote_ip', '1.2.3.4')
        ->setAttribute('country', 'USA');

    $span->addEvent('found_login' . $i, new Attributes([
        'id' => $i,
        'username' => 'otuser' . $i,
    ]));
    $span->addEvent('generated_session', new Attributes([
        'id' => md5((string) microtime(true)),
    ]));

    try {
        throw new Exception('Record exception test event');
    } catch (Exception $exception) {
        $span->recordException($exception);
    }

    $span->end();
}
$rootSpan->end();
echo PHP_EOL . 'AlwaysOnJaegerExample complete!  See the results at http://localhost:16686/';

echo PHP_EOL;
