<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Logs;

use OpenTelemetry\API\Logs\Noop\NoopLogger;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactoryInterface;
use OpenTelemetry\SDK\Logs\Logger;
use OpenTelemetry\SDK\Logs\LoggerProvider;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Logs\LoggerProvider
 * @psalm-suppress UndefinedInterfaceMethod
 * @psalm-suppress PossiblyUndefinedMethod
 */
class LoggerProviderTest extends TestCase
{
    /** @var LogRecordProcessorInterface&MockObject $processor */
    private LogRecordProcessorInterface $processor;
    private InstrumentationScopeFactoryInterface $instrumentationScopeFactory;
    private LoggerProvider $provider;

    public function setUp(): void
    {
        $this->processor = $this->createMock(LogRecordProcessorInterface::class);
        $this->instrumentationScopeFactory = $this->createMock(InstrumentationScopeFactoryInterface::class);
        $resource = $this->createMock(ResourceInfo::class);
        $this->provider = new LoggerProvider($this->processor, $this->instrumentationScopeFactory, $resource);
    }

    public function test_get_logger(): void
    {
        $logger = $this->provider->getLogger('name');
        $this->assertInstanceOf(Logger::class, $logger);
    }

    public function test_get_logger_after_shutdown(): void
    {
        $this->provider->shutdown();
        $logger = $this->provider->getLogger('name');
        $this->assertInstanceOf(NoopLogger::class, $logger);
    }

    public function test_shutdown_calls_processor_shutdown(): void
    {
        $this->processor->expects($this->once())->method('shutdown');
        $this->provider->shutdown();
    }

    public function test_force_flush_calls_processor_force_flush(): void
    {
        $this->processor->expects($this->once())->method('forceFlush');
        $this->provider->forceFlush();
    }
}
