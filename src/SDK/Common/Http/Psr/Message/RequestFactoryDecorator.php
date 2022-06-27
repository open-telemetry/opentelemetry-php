<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Http\Psr\Message;

use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

final class RequestFactoryDecorator implements RequestFactoryDecoratorInterface
{
    use RequestFactoryDecoratorTrait;

    public function __construct(RequestFactoryInterface $decorated, TextMapPropagatorInterface $propagator)
    {
        $this->decorated = $decorated;
        $this->propagator = $propagator;
    }

    public static function create(RequestFactoryInterface $decorated, TextMapPropagatorInterface $propagator): self
    {
        return new self($decorated, $propagator);
    }

    public function createRequest(string $method, $uri): RequestInterface
    {
        return self::doCreateRequest(
            $this->decorated,
            $this->propagator,
            $method,
            $uri
        );
    }
}
