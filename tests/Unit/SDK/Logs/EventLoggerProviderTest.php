<?php

declare(strict_types=1);

namespace Unit\SDK\Logs;

use OpenTelemetry\API\Logs\LoggerInterface;
use OpenTelemetry\API\Logs\LoggerProviderInterface;
use OpenTelemetry\SDK\Logs\EventLoggerProvider;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Logs\EventLoggerProvider
 */
class EventLoggerProviderTest extends TestCase
{
    public function test_emit(): void
    {
        $loggerProvider = $this->createMock(LoggerProviderInterface::class);
        $logger = $this->createMock(LoggerInterface::class);
        $loggerProvider->expects($this->once())->method('getLogger')->willReturn($logger);
        $eventLoggerProvider = new EventLoggerProvider($loggerProvider);

        $eventLoggerProvider->getEventLogger('event.logger', '1.0', 'https://example.org/schema', ['foo' => 'foo']);
    }
}
