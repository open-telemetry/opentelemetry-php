<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use OpenTelemetry\Contrib\Jaeger\Exporter as JaegerExporter;
use OpenTelemetry\Contrib\Zipkin\Exporter as ZipkinExporter;
use OpenTelemetry\SDK\SystemClock as SystemClock;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler as AlwaysOnSampler;
# use OpenTelemetry\Sdk\Trace\Sampler as AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\SamplingResult;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;
use OpenTelemetry\API\Trace as API;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Check If The Application Is Under Maintenance
|--------------------------------------------------------------------------
|
| If the application is in maintenance / demo mode via the "down" command
| we will load this file so that any pre-rendered content can be shown
| instead of starting the framework, which could cause an exception.
|
*/

if (file_exists(__DIR__.'/../storage/framework/maintenance.php')) {
    require __DIR__.'/../storage/framework/maintenance.php';
}

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| this application. We just need to utilize it! We'll simply require it
| into the script here so we don't need to manually load our classes.
|
*/

require __DIR__.'/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request using
| the application's HTTP kernel. Then, we will send the response back
| to this client's browser, allowing them to enjoy our application.
|
*/
$sampler = new AlwaysOnSampler();
$samplingResult = $sampler->shouldSample(
    new Context(),
    md5((string) microtime(true)),
    'io.opentelemetry.example',
    API\SpanKind::KIND_INTERNAL
);
$zipkinExporter = new ZipkinExporter(
    'Hello World Web Server Zipkin',
    'http://docker.for.mac.localhost:9411/api/v2/spans',
    new Client(),
    new HttpFactory(),
    new HttpFactory()
);
$jaegerExporter = new JaegerExporter(
    'Hello World Web Server Jaeger',
    'http://localhost:9412/api/v2/spans',
    new Client(),
    new HttpFactory(),
    new HttpFactory()
);
if (SamplingResult::RECORD_AND_SAMPLE === $samplingResult->getDecision()) {

    $jaegerTracer = (new TracerProvider(new BatchSpanProcessor($jaegerExporter, SystemClock::getinstance()), $sampler))
        ->getTracer('io.opentelemetry.contrib.php');

    $zipkinTracer = (new TracerProvider(new BatchSpanProcessor($zipkinExporter, SystemClock::getinstance()), $sampler))
        ->getTracer('io.opentelemetry.contrib.php');

    $request = Request::createFromGlobals();
    
    $jaegerSpan = $jaegerTracer->spanBuilder($request->getUri())->addListener('SubscribedEvent','StartSpanListener')->startSpan();
    $zipkinSpan = $zipkinTracer->spanBuilder($request->getUri())->addListener('SubscribedEvent','StartSpanListener')->startSpan();
}

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
if (SamplingResult::RECORD_AND_SAMPLE === $samplingResult->getDecision()) {
    $zipkinSpan->end();
    $jaegerSpan->end();
}
