<?php

declare(strict_types=1);

nafinal mespace Unit\SDK\Logs;

use OpenTelemetry\API\Logs\LoggerInterface;
use OpenTelemetry\SDK\Logs\EventLoggerProvider;
use OpenTelemetry\SDK\Logs\LoggerProviderInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(EventLoggerProvider::class)]
class EventLoggerProviderTest extends TestCase
{
    private EventLoggerProvider $eventLoggerProvider;
    /** @var LoggerProviderInterface&MockObject $loggerProvider */
    private LoggerProviderInterface $loggerProvider;
    private LoggerInterface $logger;

    #[\Override]
    public function setUp(): void
    {
        $this->loggerProvider = $this->createMock(LoggerProviderInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->eventLoggerProvider = new EventLoggerProvider($this->loggerProvider);
    }

    public function test_emit(): void
    {
        $this->loggerProvider->expects($this->once())->method('getLogger')->willReturn($this->logger);

        $this->eventLoggerProvider->getEventLogger('event.logger', '1.0', 'https://example.org/schema', ['foo' => 'foo']);
    }

    public function test_force_flush(): void
    {
        $this->loggerProvider->expects($this->once())->method('forceFlush')->willReturn(true);

        $this->eventLoggerProvider->forceFlush();
    }
}
