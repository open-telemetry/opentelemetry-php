<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Logs;

use OpenTelemetry\SDK\Logs\LoggerSharedState;
use OpenTelemetry\SDK\Logs\LogRecordLimits;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Logs\LoggerSharedState
 * @psalm-suppress UndefinedInterfaceMethod
 */
class LoggerSharedStateTest extends TestCase
{
    private ResourceInfo $resource;
    private LogRecordProcessorInterface $processor;
    private LogRecordLimits $limits;
    private LoggerSharedState $loggerSharedState;

    public function setUp(): void
    {
        $this->resource = $this->createMock(ResourceInfo::class);
        $this->processor = $this->createMock(LogRecordProcessorInterface::class);
        $this->limits = $this->createMock(LogRecordLimits::class);
        $this->loggerSharedState = new LoggerSharedState(
            $this->resource,
            $this->limits,
            $this->processor,
        );
    }

    public function test_get_resource(): void
    {
        $this->assertSame($this->resource, $this->loggerSharedState->getResource());
    }

    public function test_get_processor(): void
    {
        $this->assertSame($this->processor, $this->loggerSharedState->getProcessor());
    }

    public function test_get_log_record_limits(): void
    {
        $this->assertSame($this->limits, $this->loggerSharedState->getLogRecordLimits());
    }

    public function test_shutdown(): void
    {
        $this->processor->expects($this->once())->method('shutdown')->willReturn(true);

        $this->assertFalse($this->loggerSharedState->hasShutdown());
        $this->assertTrue($this->loggerSharedState->shutdown());
        $this->assertTrue($this->loggerSharedState->hasShutdown());
    }

    public function test_force_flush(): void
    {
        $this->processor->expects($this->once())->method('forceFlush')->willReturn(true);

        $this->assertTrue($this->loggerSharedState->forceFlush());
    }
}
