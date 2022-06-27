<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Http\Psr\Message;

use OpenTelemetry\SDK\Common\Http\Psr\Message\RequestFactoryDecorator;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Common\Http\Psr\Message\RequestFactoryDecorator
 */
class RequestFactoryDecoratorTest extends TestCase
{
    use UsesTextMapPropagatorTrait;
    use UsesRequestFactoryTrait;

    public function test_create_request(): void
    {
        $method = 'GET';
        $uri = 'https://example.com/';

        $instance = RequestFactoryDecorator::create(
            $this->createRequestFactoryMock($method, $uri),
            $this->createPropagatorMock()
        );

        $this->assertSame(
            $method,
            $instance->createRequest($method, $uri)->getMethod()
        );
        $this->assertSame(
            $uri,
            (string) $instance->createRequest($method, $uri)->getUri()
        );
    }
}
