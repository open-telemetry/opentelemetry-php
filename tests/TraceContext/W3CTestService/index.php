<?php

declare(strict_types=1);
require __DIR__ . '/../../../vendor/autoload.php';
require __DIR__ . '/trace-context-handler.php';

use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use OpenTelemetry\API\Common\Time\Clock;
use OpenTelemetry\Contrib\Zipkin\Exporter as ZipkinExporter;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransportFactory;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpClient\Psr18Client;

main();

function main(): void
{
    $httpClient = new Psr18Client();
    $request = parseRequestFromGlobals();
    $tracer = (new TracerProvider(
        [
            new BatchSpanProcessor(
                new ZipkinExporter(
                    (new PsrTransportFactory())->create('http://zipkin:9412/api/v2/spans', 'application/json')
                ),
                Clock::getDefault()
            ),
        ],
    ))->getTracer('W3C Trace-Context Test Service');

    try {
        $response = handleTraceContext($request, $tracer, $httpClient);
    } catch (ClientExceptionInterface $e) {
        $response = new Response(500, [], $e->getMessage());
    }

    sendResponse($response);
}

function parseRequestFromGlobals(): RequestInterface
{
    // Method
    $method = $_SERVER['REQUEST_METHOD'];

    // URI
    $uri = $_SERVER['REQUEST_URI'];

    // Build headers
    $headers = [];
    foreach ($_SERVER as $key => $value) {
        if (strpos($key, 'HTTP_') === 0) {
            $headers[substr($key, 5)] = $value;
        }
    }

    $body = file_get_contents('php://input');

    return new Request($method, $uri, $headers, $body);
}

/**
 * @param ResponseInterface $response
 * @return void
 */
function sendResponse(ResponseInterface $response)
{
    // Status line
    header(sprintf('HTTP/%s %d %s', $response->getProtocolVersion(), $response->getStatusCode(), $response->getReasonPhrase()));

    // Headers
    foreach ($response->getHeaders() as $name => $values) {
        foreach ($values as $value) {
            header(sprintf('%s: %s', $name, $value), false);
        }
    }

    // Response body
    echo $response->getBody();
}
