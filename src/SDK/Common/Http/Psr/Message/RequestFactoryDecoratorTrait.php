<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Http\Psr\Message;

use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

/** @phan-file-suppress PhanUnreferencedUseNormal */
trait RequestFactoryDecoratorTrait
{
    use FactoryDecoratorTrait;

    /**
     * @param $factory RequestFactoryInterface|ServerRequestFactoryInterface
     * @param $uri UriInterface|string
     * @return RequestInterface|ServerRequestInterface
     */
    private static function doCreateRequest(
        $factory,
        TextMapPropagatorInterface $propagator,
        string $method,
        $uri,
        array $serverParams = []
    ) {
        $request = $factory instanceof ServerRequestFactoryInterface
            ? $factory->createServerRequest($method, $uri, $serverParams)
            : $factory->createRequest($method, $uri);

        $headers = [];

        $propagator->inject($headers);

        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        return $request;
    }
}
