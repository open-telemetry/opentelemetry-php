<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Http\Psr\Client;

use OpenTelemetry\SDK\Common\Http\Psr\Client\Discovery;
use OpenTelemetry\SDK\Common\Http\Psr\Client\Discovery\DiscoveryInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;

/**
 * @covers \OpenTelemetry\SDK\Common\Http\Psr\Client\Discovery
 */
class DiscoveryTest extends TestCase
{
    public function tearDown(): void
    {
        Discovery::reset();
    }

    public function test_discover_with_defaults(): void
    {
        $client = Discovery::find();

        $this->assertInstanceOf(ClientInterface::class, $client);
    }

    public function test_discover(): void
    {
        $timeout = 31415;
        $one = $this->createMock(DiscoveryInterface::class);
        $one->expects($this->once())->method('available')->willReturn(false);
        $one->expects($this->never())->method('create');
        $two = $this->createMock(DiscoveryInterface::class);
        $two->expects($this->once())->method('available')->willReturn(true);
        $two->expects($this->once())
            ->method('create')
            ->with($this->equalTo(['timeout' => $timeout]))
            ->willReturn($this->createMock(ClientInterface::class));

        Discovery::setDiscoverers([$one, $two]);
        Discovery::find(['timeout' => 31415]);
    }

    public function test_fallback(): void
    {
        Discovery::setDiscoverers([]);
        $this->assertInstanceOf(ClientInterface::class, Discovery::find());
    }
}
