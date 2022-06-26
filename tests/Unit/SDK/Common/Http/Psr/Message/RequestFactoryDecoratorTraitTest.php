<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Common\Http\Psr\Message;

use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\SDK\Common\Http\Psr\Message\RequestFactoryDecoratorTrait;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @covers \OpenTelemetry\SDK\Common\Http\Psr\Message\RequestFactoryDecoratorTrait
 */
class RequestFactoryDecoratorTraitTest extends TestCase
{
    public function test_create_request(): void
    {
        $instance = $this->createImplementation();
        $request = $this->createMock(RequestInterface::class);
        $request->method('withHeader')
            ->willReturn($request);
        $factory = $this->createMock(RequestFactoryInterface::class);
        $factory->method('createRequest')
            ->willReturn($request);
        $propagator = $this->createPropagatorMock();

        $this->assertInstanceOf(
            RequestInterface::class,
            $instance->createRequest(
                $factory,
                $propagator,
                'POST',
                'https://example.com'
            )
        );
    }
    public function test_create_server_request(): void
    {
        $instance = $this->createImplementation();
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('withHeader')
            ->willReturn($request);
        $factory = $this->createMock(ServerRequestFactoryInterface::class);
        $factory->method('createServerRequest')
            ->willReturn($request);
        $propagator = $this->createPropagatorMock();

        $this->assertInstanceOf(
            ServerRequestInterface::class,
            $instance->createRequest(
                $factory,
                $propagator,
                'POST',
                'https://example.com',
                []
            )
        );
    }

    private function createImplementation(): object
    {
        return new class() {
            use RequestFactoryDecoratorTrait;

            /**
             * @param $factory RequestFactoryInterface|ServerRequestFactoryInterface
             * @return RequestInterface|ServerRequestInterface
             */
            public static function createRequest(
                $factory,
                TextMapPropagatorInterface $propagator,
                string $method,
                $uri,
                array $serverParams = []
            ) {
                return self::doCreateRequest(
                    $factory,
                    $propagator,
                    $method,
                    $uri,
                    $serverParams
                );
            }
        };
    }

    private function createPropagatorMock(): TextMapPropagatorInterface
    {
        $propagator = $this->createMock(TextMapPropagatorInterface::class);
        $propagator->method('inject')
            ->willReturnCallback(static function (array &$carrier) {
                $carrier['foo'] = 'bar';
            });

        return $propagator;
    }
}
