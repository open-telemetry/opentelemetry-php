<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Logs;

use OpenTelemetry\API\Common\Time\Clock;
use OpenTelemetry\API\Common\Time\TestClock;
use OpenTelemetry\API\Logs\LoggerInterface;
use OpenTelemetry\API\Logs\LoggerProviderInterface;
use OpenTelemetry\API\Logs\LogRecord;
use OpenTelemetry\API\Logs\Severity;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Logs\EventLoggerProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Logs\EventLogger
 */
class EventLoggerTest extends TestCase
{
    private LoggerInterface&MockObject $logger;
    private EventLoggerProvider $eventLoggerProvider;
    private TestClock $clock;

    public function setUp(): void
    {
        $this->clock = new TestClock();
        Clock::setDefault($this->clock);
        $this->logger = $this->createMock(LoggerInterface::class);
        $loggerProvider = $this->createMock(LoggerProviderInterface::class);
        $loggerProvider->method('getLogger')->willReturn($this->logger);
        $this->eventLoggerProvider = new EventLoggerProvider($loggerProvider);
    }

    public function test_emit(): void
    {
        $this->logger->expects($this->once())->method('emit')->with($this->callback(function (LogRecord $logRecord) {
            $expected = (new LogRecord('some.payload'))
                ->setSeverityNumber(Severity::ERROR)
                ->setTimestamp(123456)
                ->setContext(Context::getCurrent())
                ->setAttributes([
                    'event.name' => 'my.event',
                    'bar' => 'bar',
                ]);
            $this->assertEquals($expected, $logRecord);

            return true;
        }));

        $eventLogger = $this->eventLoggerProvider->getEventLogger('event.logger', '1.0', 'https://example.org/schema', ['foo' => 'foo']);
        $eventLogger->emit('my.event', 'some.payload', 123456, severityNumber: Severity::ERROR, attributes: ['bar' => 'bar']);
    }

    public function test_default_values(): void
    {
        $this->logger->expects($this->once())->method('emit')->with($this->callback(function (LogRecord $logRecord) {
            $expected = (new LogRecord())
                ->setSeverityNumber(Severity::INFO)
                ->setTimestamp($this->clock->now())
                ->setContext(Context::getCurrent())
                ->setAttributes([
                    'event.name' => 'my.event',
                ]);
            $this->assertEquals($expected, $logRecord);

            return true;
        }));

        $eventLogger = $this->eventLoggerProvider->getEventLogger('event.logger');
        $eventLogger->emit('my.event');
    }

    /**
     * "The user provided Attributes MUST not take over the event.name attribute"
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.32.0/specification/logs/event-sdk.md#emit-event
     */
    public function test_event_name_attribute_is_ignored(): void
    {
        $this->logger->expects($this->once())->method('emit')->with($this->callback(function (LogRecord $logRecord) {
            $expected = (new LogRecord())
                ->setSeverityNumber(Severity::INFO)
                ->setTimestamp($this->clock->now())
                ->setContext(Context::getCurrent())
                ->setAttributes([
                    'event.name' => 'my.event',
                ]);
            $this->assertEquals($expected, $logRecord);

            return true;
        }));

        $eventLogger = $this->eventLoggerProvider->getEventLogger('event.logger');
        $eventLogger->emit('my.event', attributes: ['event.name' => 'not.my.event']);
    }
}
