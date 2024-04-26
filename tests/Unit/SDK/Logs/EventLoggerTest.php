<?php

declare(strict_types=1);

namespace Unit\SDK\Logs;

use OpenTelemetry\API\Logs\LoggerInterface;
use OpenTelemetry\API\Logs\LoggerProviderInterface;
use OpenTelemetry\API\Logs\LogRecord;
use OpenTelemetry\API\Logs\Severity;
use OpenTelemetry\SDK\Logs\EventLoggerProvider;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Logs\EventLogger
 */
class EventLoggerTest extends TestCase
{
    public function test_emit(): void
    {
        $loggerProvider = $this->createMock(LoggerProviderInterface::class);
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('emit')->with($this->callback(function (LogRecord $logRecord) {
            $expected = (new LogRecord('some.payload'))
                ->setSeverityNumber(Severity::ERROR)
                ->setTimestamp(123456)
                ->setAttributes([
                    'event.name' => 'my.event',
                    'bar' => 'bar',
                ]);
            $this->assertEquals($expected, $logRecord);

            return true;
        }));
        $loggerProvider->expects($this->once())->method('getLogger')->willReturn($logger);
        $eventLoggerProvider = new EventLoggerProvider($loggerProvider);
        $eventLogger = $eventLoggerProvider->getEventLogger('event.logger', '1.0', 'https://example.org/schema', ['foo' => 'foo']);

        $eventLogger->emit('my.event', 'some.payload', 123456, severityNumber: Severity::ERROR, attributes: ['bar' => 'bar']);
    }
}
