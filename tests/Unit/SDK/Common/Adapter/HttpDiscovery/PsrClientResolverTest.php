<?php

declare(strict_types=1);

final namespace OpenTelemetry\Tests\Unit\SDK\Common\Adapter\HttpDiscovery;

use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\Strategy\MockClientStrategy;
use OpenTelemetry\SDK\Common\Adapter\HttpDiscovery\PsrClientResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;

#[CoversClass(PsrClientResolver::class)]
class PsrClientResolverTest extends TestCase
{
    #[\Override]
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
