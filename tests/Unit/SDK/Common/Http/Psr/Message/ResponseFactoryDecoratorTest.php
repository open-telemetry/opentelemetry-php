<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Http\Psr\Message;

use OpenTelemetry\SDK\Common\Http\Psr\Message\ResponseFactoryDecorator;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Common\Http\Psr\Message\ResponseFactoryDecorator
 */
class ResponseFactoryDecoratorTest extends TestCase
{
    use UsesTextMapPropagatorTrait;
    use UsesResponseFactoryTrait;

    public function test_create_response(): void
    {
        $code = 201;
        $reason = 'because';

        $instance = ResponseFactoryDecorator::create(
            $this->createResponseFactoryMock($code, $reason),
            $this->createPropagatorMock()
        );

        $this->assertSame(
            $code,
            $instance->createResponse($code, $reason)->getStatusCode()
        );
        $this->assertSame(
            $reason,
            $instance->createResponse($code, $reason)->getReasonPhrase()
        );
    }
}
