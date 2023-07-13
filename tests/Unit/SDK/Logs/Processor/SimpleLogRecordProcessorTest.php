<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Logs\Processor;

use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;
use OpenTelemetry\SDK\Logs\Processor\SimpleLogRecordProcessor;
use OpenTelemetry\SDK\Logs\ReadWriteLogRecord;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Logs\Processor\SimpleLogRecordProcessor
 * @psalm-suppress UndefinedInterfaceMethod
 * @psalm-suppress  PossiblyUndefinedMethod
 */
class SimpleLogRecordProcessorTest extends TestCase
{
    private SimpleLogRecordProcessor $processor;
    /** @var LogRecordExporterInterface&MockObject $exporter */
    private LogRecordExporterInterface $exporter;
    private ReadWriteLogRecord $readWriteLogRecord;

    public function setUp(): void
    {
        $this->exporter = $this->createMock(LogRecordExporterInterface::class);
        $this->readWriteLogRecord = $this->createMock(ReadWriteLogRecord::class);
        $this->processor = new SimpleLogRecordProcessor($this->exporter);
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
