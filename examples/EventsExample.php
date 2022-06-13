<?php

declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use OpenTelemetry\Contrib\Zipkin\Exporter as ZipkinExporter;
use OpenTelemetry\SDK\Common\Event\Dispatcher;
use OpenTelemetry\SDK\Common\Event\Event\ErrorEvent;
use OpenTelemetry\SDK\Common\Event\EventType;
use OpenTelemetry\SDK\Common\Event\SimpleDispatcher;
use OpenTelemetry\SDK\Common\Event\SimpleListenerProvider;
use OpenTelemetry\SDK\Common\Log\LoggerHolder;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;
use Psr\Log\LogLevel;

/**
 * This example shows how you can hook in to OpenTelemetry's events, such as when an error or warning is emitted by
 * the library. OpenTelemetry will always attempt to log and continue on error, and may provide no-op implementations
 * of classes if it was unable to create them as requested.
 * The default event handlers will use a PSR-3 logger to log events - verbosity and output format can be controlled
 * through the usual PSR-3 log levels and formatters (if available).
 * If you wish to do something different with events, you may provide an alternative PSR-14 implementation, but be
 * aware that you will need to register handlers for all events that you are interested in.
 */

//logger used by default event handlers
LoggerHolder::set(
    new Logger('otel-php', [(new StreamHandler(STDOUT, LogLevel::DEBUG))->setFormatter(new JsonFormatter())])
);

/**
 * Register event listeners - this only works for SimpleEventDispatcher. For other PSR-14 implementations, you will
/* need to register all events that you are interested in, @see {SDK\Event\EventTypes}
 */
$listenerProvider = new SimpleListenerProvider();
$listenerProvider->listen(EventType::ERROR, function (ErrorEvent $event) {
    echo 'Custom handling of an error event: ' . $event->getError()->getMessage() . PHP_EOL;
}, -10); //runs before built-in handler
$listenerProvider->listen(EventType::ERROR, function (ErrorEvent $event) {
    echo 'Another custom handling of an error event: ' . $event->getMessage() . PHP_EOL;
    echo json_encode($event->getError()->getTrace()) . PHP_EOL;
    echo 'Stopping event propagation...' . PHP_EOL;
    $event->stopPropagation();
}, 5); //runs after build-in handler
$listenerProvider->listen(EventType::ERROR, function (ErrorEvent $event) {
    echo 'This will not be executed, because a high priority handler stopped event propagation.' . PHP_EOL;
}, 10);

Dispatcher::setInstance(new SimpleDispatcher($listenerProvider));

//or, provide your own PSR-14 event dispatcher. This must be done before getting a tracer:
//$listenerProvider = new \Any\Psr14\ListenerProvider();
//$listenerProvider->listen(EventType::ERROR, function(ErrorEvent $event){...});
//DispatcherHolder::setInstance(\Any\Psr14\EventDispatcherInterface($listenerProvider));

$tracerProvider =  new TracerProvider(
    new SimpleSpanProcessor(
        ZipkinExporter::fromConnectionString('http://invalid-host:9999', 'zipkin-exporter')
    )
);
$tracer = $tracerProvider->getTracer();

$span = $tracer->spanBuilder('root')->startSpan();
$span->end();
