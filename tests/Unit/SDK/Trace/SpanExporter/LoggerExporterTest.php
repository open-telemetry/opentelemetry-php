<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace\SpanExporter;

use Exception;
use OpenTelemetry\SDK\Trace\SpanExporter\LoggerExporter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\OpenTelemetry\SDK\Trace\SpanExporter\LoggerExporter::class)]
class LoggerExporterTest extends TestCase
{
    use LoggerAwareTestTrait;

    private const SERVICE_NAME = 'LoggerExporterTest';
    private const LOG_LEVEL = 'debug';

    public function createExporter(): LoggerExporter
    {
        return new LoggerExporter(self::SERVICE_NAME);
    }

    /**
     * @psalm-suppress PossiblyUndefinedMethod
     * @psalm-suppress PossiblyInvalidArgument
     */
    public function test_export_granularity_aggregate(): void
    {
        $this->getLoggerInterfaceMock()
            ->expects($this->once())
            ->method('log');

        $this->assertTrue(
            $this->createLoggerExporter(LoggerExporter::GRANULARITY_AGGREGATE)
                ->export(
                    $this->createSpanMocks()
                )
                ->await(),
        );
    }

    /**
     * @psalm-suppress PossiblyUndefinedMethod
     * @psalm-suppress PossiblyInvalidArgument
     */
    public function test_export_granularity_span(): void
    {
        $spans = $this->createSpanMocks();

        $this->getLoggerInterfaceMock()
            ->expects($this->exactly(count($spans)))
            ->method('log');

        $this->assertTrue(
            $this->createLoggerExporter(LoggerExporter::GRANULARITY_SPAN)
                ->export($spans)
                ->await(),
        );
    }

    /**
     * @psalm-suppress PossiblyUndefinedMethod
     * @psalm-suppress PossiblyInvalidArgument
     */
    public function test_logger_throws_exception(): void
    {
        $this->getLoggerInterfaceMock()
            ->method('log')
            ->willThrowException(new Exception());

        $this->assertFalse(
            $this->createLoggerExporter()
                ->export(
                    $this->createSpanMocks()
                )
                ->await(),
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
