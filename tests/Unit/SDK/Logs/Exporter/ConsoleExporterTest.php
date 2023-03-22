<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Logs\Exporter;

use OpenTelemetry\API\Logs\LogRecord;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Logs\Exporter\ConsoleExporter;
use OpenTelemetry\SDK\Logs\LoggerSharedState;
use OpenTelemetry\SDK\Logs\ReadableLogRecord;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Logs\Exporter\ConsoleExporter
 * @psalm-suppress UndefinedInterfaceMethod
 */
class ConsoleExporterTest extends TestCase
{
    private TransportInterface $transport;
    private ConsoleExporter $exporter;

    public function setUp(): void
    {
        $this->transport = $this->createMock(TransportInterface::class);
        $this->exporter = new ConsoleExporter($this->transport);
    }

    public function test_export(): void
    {
        $batch = [
            (new ReadableLogRecord(
                $this->createMock(InstrumentationScopeInterface::class),
                $this->createMock(LoggerSharedState::class),
                (new LogRecord('foo')),
                true,
            )),
        ];

        $this->transport->expects($this->once())->method('send');

        $this->exporter->export($batch);
    }

    public function test_force_flush(): void
    {
        $this->assertTrue($this->exporter->forceFlush());
    }

    public function test_shutdown(): void
    {
        $this->assertTrue($this->exporter->shutdown());
    }
}
