<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Adapter\HttpDiscovery;

use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\Strategy\MockClientStrategy;
use OpenTelemetry\SDK\Common\Adapter\HttpDiscovery\PsrClientResolver;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;

/**
 * @covers \OpenTelemetry\SDK\Common\Adapter\HttpDiscovery\PsrClientResolver
 */
class PsrClientResolverTest extends TestCase
{
    public function setUp(): void
    {
        HttpClientDiscovery::prependStrategy(MockClientStrategy::class);
    }

    public function test_resolve_psr_client(): void
    {
        $dependency = $this->createMock(ClientInterface::class);
        $instance = PsrClientResolver::create($dependency);

        $this->assertSame(
            $dependency,
            $instance->resolvePsrClient()
        );
    }
}
