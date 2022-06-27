<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Http\Psr\Message;

use OpenTelemetry\SDK\Common\Http\Psr\Message\ServerRequestFactoryDecorator;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Common\Http\Psr\Message\ServerRequestFactoryDecorator
 */
class ServerRequestFactoryDecoratorTest extends TestCase
{
    use UsesTextMapPropagatorTrait;
    use UsesServerRequestFactoryTrait;

    public function test_create_server_request(): void
    {
        $method = 'GET';
        $uri = 'https://example.com/';

        $instance = ServerRequestFactoryDecorator::create(
            $this->createServerRequestFactoryMock($method, $uri),
            $this->createPropagatorMock()
        );

        $this->assertSame(
            $method,
            $instance->createServerRequest($method, $uri)->getMethod()
        );
        $this->assertSame(
            $uri,
            (string) $instance->createServerRequest($method, $uri)->getUri()
        );
    }
}
