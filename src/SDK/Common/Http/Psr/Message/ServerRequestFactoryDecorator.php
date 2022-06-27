<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Http\Psr\Message;

use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ServerRequestFactoryDecorator implements ServerRequestFactoryDecoratorInterface
{
    use RequestFactoryDecoratorTrait;

    public function __construct(ServerRequestFactoryInterface $decorated, TextMapPropagatorInterface $propagator)
    {
        $this->decorated = $decorated;
        $this->propagator = $propagator;
    }

    public static function create(ServerRequestFactoryInterface $decorated, TextMapPropagatorInterface $propagator): self
    {
        return new self($decorated, $propagator);
    }

    /**
     * @psalm-suppress MoreSpecificReturnType
     * @psalm-suppress LessSpecificReturnStatement
     */
    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        return self::doCreateRequest(
            $this->decorated,
            $this->propagator,
            $method,
            $uri,
            $serverParams
        );
    }
}
