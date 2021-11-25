<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Trace\SpanExporter;

use Exception;
use OpenTelemetry\SDK\Trace\SpanExporter\LoggerExporter;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;

class LoggerExporterTest extends AbstractExporterTest
{
    use LoggerAwareTestTrait;

    private const SERVICE_NAME = 'LoggerExporterTest';
    private const LOG_LEVEL = 'debug';
    private const LOG_FILE = 'debug.log';

    public function createExporter(): LoggerExporter
    {
        return new LoggerExporter(self::SERVICE_NAME);
    }

    public function testFromConnectionString(): void
    {
        /** @noinspection UnnecessaryAssertionInspection */
        $this->assertInstanceOf(
            LoggerExporter::class,
            LoggerExporter::fromConnectionString(
                self::LOG_FILE,
                self::SERVICE_NAME,
                self::LOG_LEVEL
            )
        );
    }

    /**
     * @psalm-suppress PossiblyUndefinedMethod
     * @psalm-suppress PossiblyInvalidArgument
     */
    public function testExportGranularityAggregate(): void
    {
        $this->getLoggerInterfaceMock()
            ->expects($this->once())
            ->method('log');

        $this->assertSame(
            SpanExporterInterface::STATUS_SUCCESS,
            $this->createLoggerExporter(LoggerExporter::GRANULARITY_AGGREGATE)
                ->export(
                    $this->createSpanMocks()
                )
        );
    }

    /**
     * @psalm-suppress PossiblyUndefinedMethod
     * @psalm-suppress PossiblyInvalidArgument
     */
    public function testExportGranularitySpan(): void
    {
        $spans = $this->createSpanMocks();

        $this->getLoggerInterfaceMock()
            ->expects($this->exactly(count($spans)))
            ->method('log');

        $this->assertSame(
            SpanExporterInterface::STATUS_SUCCESS,
            $this->createLoggerExporter(LoggerExporter::GRANULARITY_SPAN)
                ->export($spans)
        );
    }

    /**
     * @psalm-suppress PossiblyUndefinedMethod
     * @psalm-suppress PossiblyInvalidArgument
     */
    public function testLoggerThrowsException(): void
    {
        $this->getLoggerInterfaceMock()
            ->method('log')
            ->willThrowException(new Exception());

        $this->assertSame(
            SpanExporterInterface::STATUS_FAILED_NOT_RETRYABLE,
            $this->createLoggerExporter()
                ->export(
                    $this->createSpanMocks()
                )
        );
    }

    /**
     * @psalm-suppress PossiblyInvalidArgument
     */
    private function createLoggerExporter(int $granularity = 1): LoggerExporter
    {
        return new LoggerExporter(
            self::SERVICE_NAME,
            $this->getLoggerInterfaceMock(),
            self::LOG_LEVEL,
            $this->createSpanConverterInterfaceMock(),
            $granularity
        );
    }
}
