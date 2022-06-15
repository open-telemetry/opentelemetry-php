<?php

declare(strict_types=1);
require_once __DIR__ . '/../vendor/autoload.php';

use App\Kernel;
use OpenTelemetry\Contrib\Jaeger\Exporter as JaegerExporter;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpFoundation\Request;

(new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');

$httpClient = new Psr18Client();

$tracer = (new TracerProvider([
    new BatchSpanProcessor(
        new JaegerExporter(
            'W3C Trace-Context Test Service',
            'http://localhost:9412/api/v2/spans',
            $httpClient,
            $httpClient,
            $httpClient,
        )
    ),
]))->getTracer('W3C Trace-Context Test Service');

$request = Request::createFromGlobals();
$span = $tracer->spanBuilder($request->getUri())->startSpan();
$spanScope = $span->activate();

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);

$span->end();
$spanScope->detach();
