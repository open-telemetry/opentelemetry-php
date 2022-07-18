<?php

declare(strict_types=1);
require 'vendor/autoload.php';

use DI\Bridge\Slim\Bridge;
use DI\ContainerBuilder;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\Utils;
use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Trace\Tracer;
use OpenTelemetry\SDK\Trace\TracerProviderFactory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteContext;

$tracerProvider = (new TracerProviderFactory('example'))->create();
$tracer = $tracerProvider->getTracer('io.opentelemetry.contrib.php');

$cb = new ContainerBuilder();
$container = $cb->addDefinitions([
    Tracer::class => $tracer,
    Client::class => function () use ($tracer) {
        $stack = HandlerStack::create();
        //a guzzle middleware to wrap http calls in a span, and inject trace headers
        $stack->push(function (callable $handler) use ($tracer) {
            return function (RequestInterface $request, array $options) use ($handler, $tracer): PromiseInterface {
                $span = $tracer
                    ->spanBuilder(sprintf('%s %s', $request->getMethod(), $request->getUri()))
                    ->setSpanKind(SpanKind::KIND_CLIENT)
                    ->setAttribute('http.method', $request->getMethod())
                    ->setAttribute('http.url', $request->getUri())
                    ->startSpan();
                $ctx = $span->storeInContext(Context::getCurrent());
                $carrier = [];
                TraceContextPropagator::getInstance()->inject($carrier, null, $ctx);
                //inject traceparent and tracestate headers
                foreach ($carrier as $name => $value) {
                    $request = $request->withAddedHeader($name, $value);
                }
                $promise = $handler($request, $options);
                $promise->then(function (Response $response) use ($span) {
                    $span->setAttribute('http.status_code', $response->getStatusCode())
                        ->setAttribute('http.response_content_length', $response->getHeaderLine('Content-Length') ?: $response->getBody()->getSize())
                        ->setStatus($response->getStatusCode() < 500 ? StatusCode::STATUS_OK : StatusCode::STATUS_ERROR)
                        ->end();

                    return $response;
                }, function (\Throwable $t) use ($span) {
                    $span->recordException($t)->setStatus(StatusCode::STATUS_ERROR)->end();

                    throw $t;
                });

                return $promise;
            };
        });

        return new Client(['handler' => $stack, 'http_errors' => false]);
    },
])->build();
$app = Bridge::create($container);

//middleware starts root span based on route pattern, sets status from http code
$app->add(function (Request $request, RequestHandler $handler) use ($tracer) {
    $parent = TraceContextPropagator::getInstance()->extract($request->getHeaders());
    $routeContext = RouteContext::fromRequest($request);
    $route = $routeContext->getRoute();
    $root = $tracer->spanBuilder($route->getPattern())
        ->setStartTimestamp((int) ($request->getServerParams()['REQUEST_TIME_FLOAT'] * 1e9))
        ->setParent($parent)
        ->setSpanKind(SpanKind::KIND_SERVER)
        ->startSpan();
    $root->activate();
    $response = $handler->handle($request);
    $root->setStatus($response->getStatusCode() < 500 ? StatusCode::STATUS_OK : StatusCode::STATUS_ERROR);
    $root->end();

    return $response;
});
$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

//route for service-one
$app->get('/users/{name}', function ($name, Client $client, Response $response) {
    $promises = [
        'two'   => $client->getAsync('http://service-two:8000/two/' . $name),
        'three' => $client->getAsync('http://service-three:8000/three'),
        'other' => $client->getAsync('http://httpbin.org/get?foo=bar'),
    ];
    $responses = Utils::unwrap($promises);
    foreach ($responses as $res) {
        $response->getBody()->write($res->getBody()->getContents());
    }

    return $response;
});

//route for service-two
$app->get('/two/{name}', function (Response $response, $name) use ($tracer) {
    $span = $tracer
        ->spanBuilder('get-user')
        ->setAttribute('db.system', 'mysql')
        ->setAttribute('db.name', 'users')
        ->setAttribute('db.user', 'some_user')
        ->setAttribute('db.statement', 'select * from users where username = :1')
        ->startSpan();
    usleep((int) (0.3 * 1e6));
    $span->setStatus(StatusCode::STATUS_OK)->end();
    $response->getBody()->write(\json_encode(['some' => 'data', 'user' => $name]));

    return $response->withAddedHeader('Content-Type', 'application/json');
});

//route for service-three
$app->get('/three', function (Response $response) {
    usleep((int) (0.2 * 1e6));
    $response->getBody()->write(\json_encode(['error' => 'foo']));

    return $response->withStatus(500)->withAddedHeader('Content-Type', 'application/json');
});

$app->run();
$tracerProvider->shutdown();
