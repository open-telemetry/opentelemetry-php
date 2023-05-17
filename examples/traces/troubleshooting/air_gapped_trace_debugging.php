<?php

declare(strict_types=1);

namespace OpenTelemetry\Example;

require __DIR__ . '/../../../vendor/autoload.php';

/**
 * If you want/need to test or debug the creation of traces and spans without an instance of an exporter/collector,
 * you can use the LoggerExporter to send the span data to a Logger of your choice. The LoggerExporter can work
 * with any Logger implementing the PSR3 standard, for example Monolog, however for this example we use the
 * SimplePsrFileLogger, the library ships with.
 *
 * Note that the spans are logged in the order they are closed, so in this example the root span will be the last log entry
 */

use OpenTelemetry\SDK\Logs\SimplePsrFileLogger;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\SpanExporter\LoggerExporter;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;

/**
 * Create the log directory
 */
$logDir = __DIR__ . '/../var/log';
$logFile = $logDir . '/otel.log';
if (!is_dir($logDir) && !mkdir($logDir, 0744, true) && !is_dir($logDir)) {
    throw new \RuntimeException(sprintf('Directory "%s" was not created', $logDir));
}
/**
 * Create the LoggerExporter
 */
$exporter = new LoggerExporter(
    'Logger Example',
    new SimplePsrFileLogger($logFile)
);
/**
 * If you want to have all spans in one log entry, remove the next line
 */
$exporter->setGranularity(LoggerExporter::GRANULARITY_SPAN);
/**
 * Create the Tracer
 */
$tracerProvider = new TracerProvider(
    new SimpleSpanProcessor($exporter),
    new AlwaysOnSampler()
);
$tracer = $tracerProvider->getTracer('io.opentelemetry.contrib.php');
/**
 * Create some tracing data
 */
echo 'Start Logging ...' . PHP_EOL;
$rootSpan = $tracer->spanBuilder('root')->startSpan();
$scope = $rootSpan->activate();

$spans = [];

for ($i = 0; $i < 5; $i++) {
    usleep(200000);

    $span = $tracer->spanBuilder('log.span' . ($i + 1))->startSpan();

    usleep(20000);

    $span->addEvent('something_happened' . ($i + 1));

    usleep(20000);

    $spans[] = $span;
}

$spans = array_reverse($spans);
foreach ($spans as $span) {
    usleep(200000);
    $span->end();
}

$rootSpan->end();
$scope->detach();
echo 'Finished!' . PHP_EOL;
$tracerProvider->shutdown();
