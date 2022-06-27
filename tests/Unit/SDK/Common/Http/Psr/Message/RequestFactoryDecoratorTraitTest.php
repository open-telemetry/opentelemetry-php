<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Http\Psr\Message;

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
    use UsesTextMapPropagatorTrait;
    use UsesRequestFactoryTrait;
    use UsesServerRequestFactoryTrait;

    public function test_create_request(): void
    {
        $instance = $this->createImplementation();
        $factory = $this->createRequestFactoryMock();
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
        $factory = $this->createServerRequestFactoryMock();
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
}
