<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace\SpanExporter;

use OpenTelemetry\SDK\Common\Future\CompletedFuture;
use OpenTelemetry\SDK\Trace\SpanExporter\LoggerDecorator;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LogLevel;
use RuntimeException;

/**
 * @covers \OpenTelemetry\SDK\Trace\SpanExporter\LoggerDecorator
 */
class LoggerDecoratorTest extends AbstractLoggerAwareTest
{
    /**
     * @var SpanExporterInterface|null
     */
    private ?SpanExporterInterface $decorated;

    public function test_from_connection_string(): void
    {
        $this->expectException(RuntimeException::class);

        LoggerDecorator::fromConnectionString('foo', 'bar', 'baz');
    }

    /**
     * @psalm-suppress PossiblyUndefinedMethod
     */
    public function test_shut_down(): void
    {
        $this->getSpanExporterInterfaceMock()
            ->expects($this->once())
            ->method('shutdown')
            ->willReturn(true);

        $this->assertTrue(
            $this->createLoggerDecorator()->shutdown()
        );
    }

    /**
     * @psalm-suppress PossiblyUndefinedMethod
     */
    public function test_force_flush(): void
    {
        $this->getSpanExporterInterfaceMock()
            ->expects($this->once())
            ->method('forceFlush')
            ->willReturn(true);

        $this->assertTrue(
            $this->createLoggerDecorator()->forceFlush()
        );
    }

    /**
     * @psalm-suppress PossiblyUndefinedMethod
     * @psalm-suppress PossiblyInvalidArgument
     */
    public function test_export_success(): void
    {
        $this->getSpanExporterInterfaceMock()
            ->expects($this->once())
            ->method('export')
            ->willReturn(new CompletedFuture(SpanExporterInterface::STATUS_SUCCESS));

        $this->getLoggerInterfaceMock()
            ->expects($this->once())
            ->method('log')
            ->with(LogLevel::INFO);

        $this->createLoggerDecorator()
            ->export(
                $this->createSpanMocks()
            )
            ->await();
    }

    /**
     * @psalm-suppress PossiblyUndefinedMethod
     * @psalm-suppress PossiblyInvalidArgument
     */
    public function test_export_failed_retryable(): void
    {
        $this->getSpanExporterInterfaceMock()
            ->expects($this->once())
            ->method('export')
            ->willReturn(new CompletedFuture(SpanExporterInterface::STATUS_FAILED_RETRYABLE));

        $this->getLoggerInterfaceMock()
            ->expects($this->once())
            ->method('log')
            ->with(LogLevel::ERROR);

        $this->createLoggerDecorator()
            ->export(
                $this->createSpanMocks()
            )
            ->await();
    }

    /**
     * @psalm-suppress PossiblyUndefinedMethod
     * @psalm-suppress PossiblyInvalidArgument
     */
    public function test_export_failed_not_retryable(): void
    {
        $this->getSpanExporterInterfaceMock()
            ->expects($this->once())
            ->method('export')
            ->willReturn(new CompletedFuture(SpanExporterInterface::STATUS_FAILED_NOT_RETRYABLE));

        $this->getLoggerInterfaceMock()
            ->expects($this->once())
            ->method('log')
            ->with(LogLevel::ALERT);

        $this->createLoggerDecorator()
            ->export(
                $this->createSpanMocks()
            )
            ->await();
    }

    /**
     * @return LoggerDecorator
     * @psalm-suppress PossiblyInvalidArgument
     */
    private function createLoggerDecorator(): LoggerDecorator
    {
        return new LoggerDecorator(
            $this->getSpanExporterInterfaceMock(),
            $this->getLoggerInterfaceMock(),
            $this->createSpanConverterInterfaceMock()
        );
    }

    /**
     * @return SpanExporterInterface|MockObject
     * @psalm-suppress MismatchingDocblockReturnType
     */
    private function getSpanExporterInterfaceMock(): SpanExporterInterface
    {
        // @phpstan-ignore-next-line
        return $this->decorated ?? $this->decorated = $this->createMock(SpanExporterInterface::class);
    }
}
