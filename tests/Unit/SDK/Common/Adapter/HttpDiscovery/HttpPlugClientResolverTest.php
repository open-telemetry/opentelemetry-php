<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Adapter\HttpDiscovery;

use Http\Client\HttpAsyncClient;
use OpenTelemetry\SDK\Common\Adapter\HttpDiscovery\HttpPlugClientResolver;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Common\Adapter\HttpDiscovery\HttpPlugClientResolver
 */
class HttpPlugClientResolverTest extends TestCase
{
    public function test_resolve_http_plug_async_client(): void
    {
        $dependency = $this->createMock(HttpAsyncClient::class);
        $instance = HttpPlugClientResolver::create($dependency);

        $this->assertSame(
            $dependency,
            $instance->resolveHttpPlugAsyncClient()
        );
    }
}
