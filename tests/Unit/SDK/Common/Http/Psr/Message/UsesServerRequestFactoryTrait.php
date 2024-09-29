<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Http\Psr\Message;

use Nyholm\Psr7\Uri;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

trait UsesServerRequestFactoryTrait
{
    use CreatesMockTrait;

    private function createServerRequestFactoryMock(?string $requestMethod = 'GET', ?string $requestUri = ''): ServerRequestFactoryInterface
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('withHeader')
            ->willReturn($request);
        $request->method('getMethod')
            ->willReturn((string) $requestMethod);
        $request->method('getUri')
            ->willReturn(new Uri((string) $requestUri));
        $factory = $this->createMock(ServerRequestFactoryInterface::class);
        $factory->method('createServerRequest')
            ->willReturn($request);

        return $factory;
    }
}
