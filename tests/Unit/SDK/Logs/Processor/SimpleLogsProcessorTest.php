<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Logs\Processor;

use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;
use OpenTelemetry\SDK\Logs\Processor\SimpleLogsProcessor;
use OpenTelemetry\SDK\Logs\ReadWriteLogRecord;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Logs\Processor\SimpleLogsProcessor
 * @psalm-suppress UndefinedInterfaceMethod
 * @psalm-suppress  PossiblyUndefinedMethod
 */
class SimpleLogsProcessorTest extends TestCase
{
    private SimpleLogsProcessor $processor;
    /**
     * @var LogRecordExporterInterface|(LogRecordExporterInterface&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
     */
    private LogRecordExporterInterface $exporter;
    private ReadWriteLogRecord $readWriteLogRecord;

    public function setUp(): void
    {
        $this->exporter = $this->createMock(LogRecordExporterInterface::class);
        $this->readWriteLogRecord = $this->createMock(ReadWriteLogRecord::class);
        $this->processor = new SimpleLogsProcessor($this->exporter);
    }

    public function test_on_emit(): void
    {
        $this->exporter->expects($this->once())->method('export')->with($this->equalTo([$this->readWriteLogRecord]));

        $this->processor->onEmit($this->readWriteLogRecord);
    }

    public function test_shutdown(): void
    {
        $this->exporter->expects($this->once())->method('shutdown');

        $this->processor->shutdown();
    }

    public function test_force_flush(): void
    {
        $this->exporter->expects($this->once())->method('forceFlush');

        $this->processor->forceFlush();
    }
}
