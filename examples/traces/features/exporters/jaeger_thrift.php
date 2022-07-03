<?php

declare(strict_types=1);
require __DIR__ . '/../../../../vendor/autoload.php';

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use OpenTelemetry\Contrib\Jaeger\AgentExporter;
use OpenTelemetry\SDK\Common\Log\LoggerHolder;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;
use Psr\Log\LogLevel;

$logger = new Logger('otel-php', [new StreamHandler(STDOUT, LogLevel::DEBUG)]);
LoggerHolder::set($logger);

$exporter = new AgentExporter('jaeger-thrift', 'jaeger:6831');
$tracerProvider = new TracerProvider(
    new SimpleSpanProcessor($exporter),
    new AlwaysOnSampler(),
);

echo 'Starting Jaeger Thrift example';

$tracer = $tracerProvider->getTracer('io.opentelemetry.contrib.php');

$rootSpan = $tracer->spanBuilder('root')->startSpan();
$rootSpan->activate();
echo PHP_EOL . sprintf(
    'Root Span: %s, Parent: %s, Span: %s',
    $rootSpan->getContext()->getTraceId(),
    $rootSpan->getParentContext()->getSpanId(),
    $rootSpan->getContext()->getSpanId()
) . PHP_EOL;

for ($i = 0; $i < 1; $i++) {
    // start a span, register some events
    $span = $tracer->spanBuilder('session.generate.span-' . $i)->startSpan();

    echo sprintf(
        PHP_EOL . 'Exporting Trace: %s, Parent: %s, Span: %s',
        $span->getContext()->getTraceId(),
        $span->getParentContext()->getSpanId() ?: 'None',
        $span->getContext()->getSpanId()
    ) . PHP_EOL;

    $span->setAttribute('remote_ip', '1.2.3.4')
        ->setAttribute('country', 'USA');

    $span->addEvent('found_login' . $i, [
        'id' => $i,
        'username' => 'otuser' . $i,
    ]);
    $span->addEvent('generated_session', [
        'id' => md5((string) microtime(true)),
    ]);

    try {
        throw new Exception('Record exception test event');
    } catch (Exception $exception) {
        $span->recordException($exception);
    }

    $span->end();
}
$rootSpan->end();
echo PHP_EOL . 'Jaeger Thrift example complete!  See the results at http://localhost:16686/';

echo PHP_EOL;
