<?php

declare(strict_types=1);

namespace App\Controller;

use GuzzleHttp\Client;
use OpenTelemetry\Sdk\Trace\PropagationMap;
use OpenTelemetry\Sdk\Trace\SpanContext;
use OpenTelemetry\Sdk\Trace\TraceContextPropagator;
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
                $context;
                $headers = ['content-type' => 'application/json'];
                $url = $case['url'];
                $arguments = $case['arguments'];

                $carrier = new PropagationMap();

                try {
                    $context = TraceContextPropagator::extract($request->headers->all(), $carrier);
                } catch (\InvalidArgumentException $th) {
                    $context = SpanContext::generate();
                }

                $span = $tracer->startAndActivateSpanFromContext($url, $context, true);
                TraceContextPropagator::inject($context, $headers, $carrier);

                $client = new Client([
                    'base_uri' => $url,
                    'timeout'  => 2.0,
                ]);

                $testServiceRequest = new \GuzzleHttp\Psr7\Request('POST', $url, $headers, json_encode($arguments));
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
