<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Logs;

use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Logs\LogRecordExporterInterface;
use OpenTelemetry\SDK\Logs\LogRecordProcessorFactory;
use OpenTelemetry\SDK\Logs\Processor\BatchLogRecordProcessor;
use OpenTelemetry\SDK\Logs\Processor\MultiLogRecordProcessor;
use OpenTelemetry\SDK\Logs\Processor\NoopLogRecordProcessor;
use OpenTelemetry\SDK\Logs\Processor\SimpleLogRecordProcessor;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[\PHPUnit\Framework\Attributes\CoversClass(\OpenTelemetry\SDK\Logs\LogRecordProcessorFactory::class)]
class LogRecordProcessorFactoryTest extends TestCase
{
    use TestState;

    /**
     * @param class-string $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('exporterProvider')]
    public function test_create(string $name, string $expected): void
    {
        $exporter = $this->createMock(LogRecordExporterInterface::class);
        $this->setEnvironmentVariable('OTEL_PHP_LOGS_PROCESSOR', $name);
        $processor = (new LogRecordProcessorFactory())->create($exporter);

        $this->assertInstanceOf($expected, $processor);
    }

    public static function exporterProvider(): array
    {
        return [
            ['batch', BatchLogRecordProcessor::class],
            ['simple', SimpleLogRecordProcessor::class],
            ['noop', NoopLogRecordProcessor::class],
            ['none', NoopLogRecordProcessor::class],
        ];
    }

    public function test_create_invalid(): void
    {
        $this->setEnvironmentVariable('OTEL_PHP_LOGS_PROCESSOR', 'baz');
        $this->expectException(\InvalidArgumentException::class);

        (new LogRecordProcessorFactory())->create($this->createMock(LogRecordExporterInterface::class));
    }

    public function test_create_multiple(): void
    {
        $this->setEnvironmentVariable('OTEL_PHP_LOGS_PROCESSOR', 'batch,simple');
        $processor = (new LogRecordProcessorFactory())->create($this->createMock(LogRecordExporterInterface::class));

        $this->assertInstanceOf(MultiLogRecordProcessor::class, $processor);
    }

    public function test_create_from_environment(): void
    {
        $expected = [
            'maxQueueSize' => 10,
            'scheduledDelayNanos' => 2 * 1_000_000,
            'maxExportBatchSize' => 1,
        ];
        $this->setEnvironmentVariable(Variables::OTEL_PHP_LOGS_PROCESSOR, 'batch');
        $this->setEnvironmentVariable(Variables::OTEL_BLRP_MAX_QUEUE_SIZE, 10);
        $this->setEnvironmentVariable(Variables::OTEL_BLRP_SCHEDULE_DELAY, 2);
        $this->setEnvironmentVariable(Variables::OTEL_BLRP_EXPORT_TIMEOUT, 3);
        $this->setEnvironmentVariable(Variables::OTEL_BLRP_MAX_EXPORT_BATCH_SIZE, 1);

        $processor = (new LogRecordProcessorFactory())->create($this->createMock(LogRecordExporterInterface::class));
        $this->assertInstanceOf(BatchLogRecordProcessor::class, $processor);

        $reflection = new ReflectionClass($processor);
        foreach ($expected as $propertyName => $value) {
            $property = $reflection->getProperty($propertyName);
            $property->setAccessible(true);
            $this->assertSame($value, $property->getValue($processor));
        }
    }
}
