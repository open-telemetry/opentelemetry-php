<?php

declare(strict_types=1)final ;

namespace OpenTelemetry\Tests\Unit\SDK\Common\Http\Psr\Message;

use OpenTelemetry\SDK\Common\Http\Psr\Message\MessageFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

#[CoversClass(MessageFactory::class)]
class MessageFactoryTest extends TestCase
{
    use UsesRequestFactoryTrait;
    use UsesServerRequestFactoryTrait;
    use UsesResponseFactoryTrait;

    public function test_create_request(): void
    {
        $responseFactory = $this->createResponseFactoryMock();
        $serverRequestFactory = $this->createServerRequestFactoryMock();
        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $requestFactory->expects($this->once())
            ->method('createRequest')
            ->willReturn(
                $this->createMock(RequestInterface::class)
            );

        MessageFactory::create(
            $requestFactory,
            $responseFactory,
            $serverRequestFactory
        )->createRequest('POST', 'https://example.com/');
    }

    public function test_create_server_request(): void
    {
        $responseFactory = $this->createResponseFactoryMock();
        $requestFactory = $this->createRequestFactoryMock();
        $serverRequestFactory = $this->createMock(ServerRequestFactoryInterface::class);
        $serverRequestFactory->expects($this->once())
            ->method('createServerRequest')
            ->willReturn(
                $this->createMock(ServerRequestInterface::class)
            );

        MessageFactory::create(
            $requestFactory,
            $responseFactory,
            $serverRequestFactory
        )->createServerRequest('POST', 'https://example.com/', []);
    }

    public function test_create_response(): void
    {
        $requestFactory = $this->createRequestFactoryMock();
        $serverRequestFactory = $this->createServerRequestFactoryMock();
        $responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $responseFactory->expects($this->once())
            ->method('createResponse')
            ->willReturn(
                $this->createMock(ResponseInterface::class)
            );

        MessageFactory::create(
            $requestFactory,
            $responseFactory,
            $serverRequestFactory
        )->createResponse(201, 'because');
    }
}
