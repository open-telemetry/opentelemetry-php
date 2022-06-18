<?php

declare(strict_types=1);

use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\Propagation\ArrayAccessGetterSetter;
use OpenTelemetry\Context\Propagation\SanitizeCombinedHeadersPropagationGetter;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpClient\Psr18Client;

/**
 * @param RequestInterface $serviceRequest
 * @param TracerInterface $tracer
 * @param Psr18Client $httpClient
 * @throws ClientExceptionInterface
 * @return ResponseInterface
 */
function handleTraceContext(RequestInterface $serviceRequest, TracerInterface $tracer, Psr18Client $httpClient): ResponseInterface
{
    if ($serviceRequest->getMethod() !== 'POST') {
        return new Response(400, [], 'Only POST requests are allowed');
    }

    $traceCtxPropagator = TraceContextPropagator::getInstance();
    $propagationGetter = new SanitizeCombinedHeadersPropagationGetter(new ArrayAccessGetterSetter());

    try {
        $context = $traceCtxPropagator->extract($serviceRequest->getHeaders(), $propagationGetter);
    } catch (InvalidArgumentException $th) {
        $context = Context::getCurrent();
    }

//    $body = json_decode($serviceRequest->getBody()->getContents(), true);
    $body = json_decode((string) $serviceRequest->getBody(), true);
    if (! is_array($body)) {
        return new Response(400, [], 'Invalid JSON');
    }

    foreach ($body as $case) {
        $headers = ['content-type' => 'application/json'];
        $url = $case['url'];
        $arguments = $case['arguments'];

        $span = $tracer->spanBuilder($url)->setParent($context)->startSpan();
        $context = $span->storeInContext($context);
        $scope = $context->activate();

        $traceCtxPropagator->inject($headers, null, $context);

        $serviceRequest = new Request('POST', $url, $headers, json_encode($arguments));
        $httpClient->sendRequest($serviceRequest);

        $span->end();
        $scope->detach();
    }

    return new Response(
        200,
        ['content-type' => 'text/html'],
        'Subsequent calls from the service have been dispatched',
    );
}
