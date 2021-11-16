<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Contrib\Unit;

use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

trait UsesHttpClientTrait
{
    private ?ClientInterface $client = null;
    private ?RequestFactoryInterface $requestFactory = null;
    private ?StreamFactoryInterface $streamFactory = null;
    private ?RequestInterface $request = null;
    private ?ResponseInterface $response = null;

    abstract protected function createMock(string $originalClassName): MockObject;

    /**
     * @return ClientInterface|MockObject
     */
    protected function getClientInterfaceMock(): ClientInterface
    {
        return $this->client instanceof ClientInterface
            ? $this->client
            : $this->client = $this->createMock(ClientInterface::class);
    }

    /**
     * @return RequestFactoryInterface|MockObject
     */
    protected function getRequestFactoryInterfaceMock(): RequestFactoryInterface
    {
        return $this->requestFactory instanceof RequestFactoryInterface
            ? $this->requestFactory
            : $this->requestFactory = $this->createRequestFactoryInterfaceMock();
    }

    /**
     * @return RequestFactoryInterface|MockObject
     */
    protected function createRequestFactoryInterfaceMock(): RequestFactoryInterface
    {
        $mock = $this->createMock(RequestFactoryInterface::class);
        $mock->method('createRequest')
            ->willReturn($this->createRequestInterfaceMock());

        return $mock;
    }

    /**
     * @return StreamFactoryInterface|MockObject
     */
    protected function getStreamFactoryInterfaceMock(): StreamFactoryInterface
    {
        return $this->streamFactory instanceof StreamFactoryInterface
            ? $this->streamFactory
            : $this->streamFactory = $this->createMock(StreamFactoryInterface::class);
    }

    /**
     * @param int $status
     * @return RequestInterface|MockObject
     */
    protected function getRequestInterfaceMock(int $status = 200): RequestInterface
    {
        return $this->request instanceof RequestInterface
            ? $this->request
            : $this->request = $this->createRequestInterfaceMock();
    }

    /**
     * @param int $status
     * @return RequestInterface|MockObject
     */
    protected function createRequestInterfaceMock(int $status = 200): RequestInterface
    {
        $mock = $this->createMock(RequestInterface::class);
        foreach ([
                 'withProtocolVersion',
                 'withHeader',
                 'withAddedHeader',
                 'withoutHeader',
                 'withBody',
             ] as $method) {
            $mock->method($method)->willReturn($mock);
        }

        return $mock;
    }

    /**
     * @param int $status
     * @return ResponseInterface|MockObject
     */
    protected function createResponseInterfaceMock(int $status = 200): ResponseInterface
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')
            ->willReturn($status);

        return $response;
    }
}
