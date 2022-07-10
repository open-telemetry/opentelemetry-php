<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Http\Psr\Message;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

trait UsesResponseFactoryTrait
{
    use CreatesMockTrait;

    private function createResponseFactoryMock(int $code = 200, string $reasonPhrase = ''): ResponseFactoryInterface
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getHeaders')
            ->willReturn(['foo' => 'bar']);
        $response->method('getStatusCode')
            ->willReturn($code);
        $response->method('getReasonPhrase')
            ->willReturn($reasonPhrase);
        $factory = $this->createMock(ResponseFactoryInterface::class);
        $factory->method('createResponse')
            ->willReturn($response);

        return $factory;
    }
}
