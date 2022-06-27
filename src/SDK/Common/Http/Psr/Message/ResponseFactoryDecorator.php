<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Http\Psr\Message;

use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

final class ResponseFactoryDecorator implements ResponseFactoryDecoratorInterface
{
    use FactoryDecoratorTrait;

    public function __construct(ResponseFactoryInterface $decorated, TextMapPropagatorInterface $propagator)
    {
        $this->decorated = $decorated;
        $this->propagator = $propagator;
    }

    public static function create(ResponseFactoryInterface $decorated, TextMapPropagatorInterface $propagator): self
    {
        return new self($decorated, $propagator);
    }

    /**
     * @inheritDoc
     */
    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        $response = $this->decorated->createResponse($code, $reasonPhrase);

        $headers = $response->getHeaders();

        $this->propagator->extract($headers);

        return $response;
    }
}
