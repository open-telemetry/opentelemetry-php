<?php

declare(strict_types=1);

namespace App\Controller;

use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\Propagation\ArrayAccessGetterSetter;
use OpenTelemetry\Context\Propagation\SanitizeCombinedHeadersPropagationGetter;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestController
{
    /**
     * @Route("/test")
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function index(Request $request): Response
    {
        global $tracer;
        if (!$tracer) {
            return new Response('internal error', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $traceCtxPropagator = TraceContextPropagator::getInstance();
        $propagationGetter = new SanitizeCombinedHeadersPropagationGetter(new ArrayAccessGetterSetter());

        try {
            $context = $traceCtxPropagator->extract($request->headers->all(), $propagationGetter);
        } catch (\InvalidArgumentException $th) {
            $context = new Context();
        }

        $body = json_decode($request->getContent(), true);
        if (!$body) {
            return new Response('Invalid JSON', Response::HTTP_BAD_REQUEST);
        }

        foreach ($body as $case) {
            $headers = ['content-type' => 'application/json'];
            $url = $case['url'];
            $arguments = $case['arguments'];

            $span = $tracer->spanBuilder($url)->setParent($context)->startSpan();
            $context = $span->storeInContext($context);
            $scope = $context->activate();

            $traceCtxPropagator->inject($headers, null, $context);

            $client = new Psr18Client(HttpClient::create(
                [
                    'base_uri' => $url,
                    'timeout'  => 2.0,
                ]
            ));

            $testServiceRequest = new \Nyholm\Psr7\Request('POST', $url, $headers, json_encode($arguments));

            $client->sendRequest($testServiceRequest);

            $span->end();
            $scope->detach();
        }

        return new Response(
            'Subsequent calls from the trace-context test service are dispatched',
            Response::HTTP_OK,
            ['content-type' => 'text/html']
        );
    }
}
