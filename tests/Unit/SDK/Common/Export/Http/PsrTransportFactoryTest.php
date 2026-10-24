<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Export\Http;

use InvalidArgumentException;
use OpenTelemetry\API\Behavior\Internal\Logging;
use OpenTelemetry\API\Behavior\Internal\LogWriter\LogWriterInterface;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransportFactory;
use OpenTelemetry\SDK\Common\Http\Psr\Client\Discovery;
use OpenTelemetry\SDK\Common\Http\Psr\Client\Discovery\DiscoveryInterface;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LogLevel;

#[CoversClass(PsrTransportFactory::class)]
final class PsrTransportFactoryTest extends TestCase
{
    use TestState;

    public function test_warns_when_client_rejects_tls_options(): void
    {
        $discoverer = $this->createMock(DiscoveryInterface::class);
        $discoverer->method('available')->willReturn(true);
        $discoverer->expects($this->exactly(2))->method('create')
            ->willReturnCallback(fn (array $options): ClientInterface => isset($options['cafile'])
                ? throw new InvalidArgumentException('unsupported option')
                : $this->createStub(ClientInterface::class));
        Discovery::setDiscoverers([$discoverer]);

        $logWriter = $this->createMock(LogWriterInterface::class);
        $logWriter->expects($this->once())->method('write')
            ->with(LogLevel::WARNING, $this->stringContains('unsupported option'));
        Logging::setLogWriter($logWriter);

        (new PsrTransportFactory())->create('http://localhost:4318', 'application/x-protobuf', cacert: '/ca.pem');
    }

    public function test_does_not_warn_without_tls_options(): void
    {
        $discoverer = $this->createMock(DiscoveryInterface::class);
        $discoverer->method('available')->willReturn(true);
        $discoverer->expects($this->once())->method('create')->willReturn($this->createStub(ClientInterface::class));
        Discovery::setDiscoverers([$discoverer]);

        $logWriter = $this->createMock(LogWriterInterface::class);
        $logWriter->expects($this->never())->method('write');
        Logging::setLogWriter($logWriter);

        (new PsrTransportFactory())->create('http://localhost:4318', 'application/x-protobuf');
    }
}
