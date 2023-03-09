<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Logs;

use OpenTelemetry\API\Logs\EventLogger;
use OpenTelemetry\API\Logs\LoggerInterface;
use OpenTelemetry\API\Logs\LogRecord;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\API\Logs\EventLogger
 */
class EventLoggerTest extends TestCase
{
    public function test_log_event(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $domain = 'some.domain';
        $logRecord = $this->createMock(LogRecord::class);
        $eventLogger = new EventLogger($logger, $domain);
        $logRecord->expects($this->once())->method('setAttributes');
        $logger->expects($this->once())->method('logRecord')->with($this->equalTo($logRecord));

        $eventLogger->logEvent('some.event', $logRecord);
    }
}
