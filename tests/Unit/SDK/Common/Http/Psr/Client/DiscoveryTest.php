<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Http\Psr\Client;

use OpenTelemetry\SDK\Common\Http\Psr\Client\Discovery;
use OpenTelemetry\SDK\Common\Http\Psr\Client\Discovery\DiscoveryInterface;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;

#[CoversClass(Discovery::class)]
class DiscoveryTest extends TestCase
{
    use TestState;

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
            ->willReturn($this->createStub(ClientInterface::class));

        Discovery::setDiscoverers([$one, $two]);
        Discovery::find(['timeout' => 31415]);
    }

    public function test_zero_timeout_is_passed_to_client(): void
    {
        $discoverer = $this->createMock(DiscoveryInterface::class);
        $discoverer->method('available')->willReturn(true);
        $discoverer->expects($this->once())
            ->method('create')
            ->with($this->identicalTo(['timeout' => 0.0]))
            ->willReturn($this->createStub(ClientInterface::class));

        Discovery::setDiscoverers([$discoverer]);
        Discovery::find(['timeout' => 0.0, 'cafile' => null]);
    }

    public function test_fallback(): void
    {
        Discovery::setDiscoverers([]);
        $this->assertInstanceOf(ClientInterface::class, Discovery::find());
    }
}
