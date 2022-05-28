<?php

declare(strict_types=1);

namespace App\Controller;

use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;
use OpenTelemetry\API\Trace\SpanContext;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestController
{
    /**
     * @Route("/test")
     */
    public function index(Request $request): Response
    {
        global $tracer;

        $array = $request->request->all();
        $body = json_decode($request->getContent(), true);

        foreach ($body as $case) {
            if ($tracer) {
                $traceCtxPropagator = TraceContextPropagator::getInstance();

                $headers = ['content-type' => 'application/json'];
                $url = $case['url'];
                $arguments = $case['arguments'];

                try {
                    $context = $traceCtxPropagator->extract($request->headers->all());
                } catch (\InvalidArgumentException $th) {
                    $context = SpanContext::getInvalid();
                }

                $span = $tracer->spanBuilder($url)->setParent($context)->startSpan();
                $span->activate();

                // ? Shouldn't we be doing something with the carrier?
                $carrier = [];
                $traceCtxPropagator->inject($carrier, null, $context);

                $client = new Psr18Client(HttpClient::create(
                    [
                        'base_uri' => $url,
                        'timeout'  => 2.0,
                    ]
                ));

                $testServiceRequest = new \Nyholm\Psr7\Request('POST', $url, $headers, json_encode($arguments));

                $response = $client->sendRequest($testServiceRequest);

                $span->end();
            }
        }

        return new Response(
            'Subsequent calls from the trace-context test service are dispatched',
            Response::HTTP_OK,
            ['content-type' => 'text/html']
        );
    }
}
