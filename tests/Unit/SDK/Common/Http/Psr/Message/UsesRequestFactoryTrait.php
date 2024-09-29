<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Http\Psr\Message;

use Nyholm\Psr7\Uri;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

trait UsesRequestFactoryTrait
{
    use CreatesMockTrait;

    private function createRequestFactoryMock(?string $requestMethod = 'GET', ?string $requestUri = ''): RequestFactoryInterface
    {
        $request = $this->createMock(RequestInterface::class);
        $request->method('withHeader')
            ->willReturn($request);
        $request->method('getMethod')
            ->willReturn((string) $requestMethod);
        $request->method('getUri')
            ->willReturn(new Uri((string) $requestUri));
        $factory = $this->createMock(RequestFactoryInterface::class);
        $factory->method('createRequest')
            ->willReturn($request);

        return $factory;
    }
}
