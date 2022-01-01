<?php

declare(strict_types=1);
require_once __DIR__ . '/../vendor/autoload.php';

use App\Kernel;
use OpenTelemetry\API\Trace as API;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Contrib\Jaeger\Exporter as JaegerExporter;
use OpenTelemetry\SDK\AbstractClock;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\SamplingResult;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;

(new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');

$sampler = new AlwaysOnSampler();
$samplingResult = $sampler->shouldSample(
    Context::getCurrent(),
    md5((string) microtime(true)),
    'io.opentelemetry.example',
    API\SpanKind::KIND_INTERNAL
);

$exporter = new JaegerExporter(
    'W3C Trace-Context Test Service',
    'http://localhost:9412/api/v2/spans'
);

if (SamplingResult::RECORD_AND_SAMPLE === $samplingResult->getDecision()) {
    $tracer = (new TracerProvider())
        ->addSpanProcessor(new BatchSpanProcessor($exporter, AbstractClock::getDefault()))
        ->getTracer('io.opentelemetry.contrib.php');

    $request = Request::createFromGlobals();
    $span = $tracer->startAndActivateSpan($request->getUri());
}

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);

if (SamplingResult::RECORD_AND_SAMPLED === $samplingResult->getDecision()) {
    $span->end();
}
