<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Trace\SpanExporter;

use OpenTelemetry\SDK\Trace\SpanConverterInterface;
use OpenTelemetry\SDK\Trace\SpanDataInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

trait LoggerAwareTestTrait
{
    /**
     * @var LoggerInterface|null
     */
    protected ?LoggerInterface $logger;

    /**
     * @return LoggerInterface|MockObject
     * @psalm-suppress MismatchingDocblockReturnType
     */
    protected function getLoggerInterfaceMock(): LoggerInterface
    {
        // @phpstan-ignore-next-line
        return $this->logger ?? $this->logger = $this->createMock(LoggerInterface::class);
    }

    protected function createSpanConverterInterfaceMock(): SpanConverterInterface
    {
        $mock = $this->createMock(SpanConverterInterface::class);
        $mock->method('convert')->willReturn([]);

        return $mock;
    }

    protected function createSpanMocks(): array
    {
        return [
            $this->createMock(SpanDataInterface::class),
            $this->createMock(SpanDataInterface::class),
        ];
    }

    abstract protected function createMock(string $originalClassName): MockObject;
}
