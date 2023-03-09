<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Logs;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;
use OpenTelemetry\SDK\Logs\LogRecordProcessorFactory;
use OpenTelemetry\SDK\Logs\Processor\BatchLogsProcessor;
use OpenTelemetry\SDK\Logs\Processor\NoopLogsProcessor;
use OpenTelemetry\SDK\Logs\Processor\SimpleLogsProcessor;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Logs\LogRecordProcessorFactory
 */
class LogRecordProcessorFactoryTest extends TestCase
{
    use EnvironmentVariables;

    public function tearDown(): void
    {
        $this->restoreEnvironmentVariables();
    }

    /**
     * @dataProvider exporterProvider
     */
    public function test_create($name, $expected): void
    {
        $exporter = $this->createMock(LogRecordExporterInterface::class);
        $this->setEnvironmentVariable('OTEL_PHP_LOGS_PROCESSOR', $name);
        $processor = (new LogRecordProcessorFactory())->create($exporter);

        $this->assertInstanceOf($expected, $processor);
    }

    public static function exporterProvider(): array
    {
        return [
            ['batch', BatchLogsProcessor::class],
            ['simple', SimpleLogsProcessor::class],
            ['noop', NoopLogsProcessor::class],
            ['none', NoopLogsProcessor::class],
        ];
    }

    public function test_create_invalid(): void
    {
        $this->setEnvironmentVariable('OTEL_PHP_LOGS_PROCESSOR', 'baz');
        $this->expectException(\InvalidArgumentException::class);

        (new LogRecordProcessorFactory())->create($this->createMock(LogRecordExporterInterface::class));
    }

    public function test_rejects_multiple(): void
    {
        $this->setEnvironmentVariable('OTEL_PHP_LOGS_PROCESSOR', 'one,two');
        $this->expectException(\InvalidArgumentException::class);

        (new LogRecordProcessorFactory())->create($this->createMock(LogRecordExporterInterface::class));
    }
}
