<?php

declare(strict_types=1);

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use OpenTelemetry\API\Trace\ContextKeys;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Log\LoggerHolder;
use OpenTelemetry\SDK\Trace\TracerProviderFactory;
use Psr\Log\LogLevel;

require __DIR__ . '/../../../vendor/autoload.php';

LoggerHolder::set(
    new Logger('otel-php', [new StreamHandler(STDOUT, LogLevel::DEBUG)])
);

//putenv('OTEL_EXPORTER_ZIPKIN_ENDPOINT=http://zipkin:9411/api/v2/spans');
putenv('OTEL_TRACES_EXPORTER=console');

$factory = new TracerProviderFactory('demo');
$tracerProvider = $factory->create();

$tracer = $tracerProvider->getTracer('io.opentelemetry.contrib.php');

/*
 * This demonstrates how to store the root span of an HTTP server request so that it can be retrieved later (e.g to update
 * its name after routing has been performed).
 * It's possible to have multiple HTTP server requests being processed within the same process, e.g Symfony's sub-request
 * feature: https://symfony.com/doc/current/components/http_kernel.html#sub-requests
 * In this example, both the main and sub-request spans are stored in context with the same key. Some other code (perhaps
 * a post-route event handler) can then retrieve the currently-active "http request span" and update its name based on the
 * routing results.
 */

$rootSpan = $tracer->spanBuilder('main')->startSpan();
$rootScope = $rootSpan->activate();
Context::storage()->attach(Context::getCurrent()->with(ContextKeys::httpServerSpan(), $rootSpan));
//main request routing completed, update span's name from routing results
Context::getCurrent()->get(ContextKeys::httpServerSpan())->updateName('main-route-name');

//sub-request
$subRequest = $tracer->spanBuilder('sub-request')->startSpan();
Context::storage()->attach(Context::getCurrent()->with(ContextKeys::httpServerSpan(), $subRequest));
$subRequestScope = $subRequest->activate();
//sub-request routing completed, update "sub-request" span's name from routing results
Context::getCurrent()->get(ContextKeys::httpServerSpan())->updateName('sub-route-name');

$subRequest->end();
$subRequestScope->detach();
Context::storage()->scope()->detach(); //sub-request span
//end sub-request

$tracer->spanBuilder('process-sub-request-results')->startSpan()->end();

$rootSpan->end();
Context::storage()->scope()->detach(); //main http server scope
$rootScope->detach(); //main span
$tracerProvider->shutdown();
