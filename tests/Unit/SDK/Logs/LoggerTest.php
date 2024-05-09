<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Logs;

use OpenTelemetry\API\Logs\LogRecord;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScope;
use OpenTelemetry\SDK\Logs\Logger;
use OpenTelemetry\SDK\Logs\LoggerSharedState;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;
use OpenTelemetry\SDK\Logs\ReadWriteLogRecord;
use PHPUnit\Framework\TestCase;

/**
 * @psalm-suppress UndefinedInterfaceMethod
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\OpenTelemetry\SDK\Logs\Logger::class)]
class LoggerTest extends TestCase
{
    private LoggerSharedState $sharedState;
    private LogRecordProcessorInterface $processor;
    private InstrumentationScope $scope;

    public function setUp(): void
    {
        $this->sharedState = $this->createMock(LoggerSharedState::class);
        $this->processor = $this->createMock(LogRecordProcessorInterface::class);
        $this->sharedState->method('getProcessor')->willReturn($this->processor);
        $this->scope = new InstrumentationScope('foo', '1.0', 'schema.url', Attributes::create([])); //final
    }

    public function test_log_record(): void
    {
        $logger = new Logger($this->sharedState, $this->scope);
        $record = (new LogRecord())->setContext($this->createMock(ContextInterface::class));

        $this->processor->expects($this->once())->method('onEmit')
            ->with(
                $this->isInstanceOf(ReadWriteLogRecord::class),
                $this->isInstanceOf(ContextInterface::class)
            );

        $logger->emit($record);
    }

    public function test_sets_observed_timestamp_on_emit(): void
    {
        $logger = new Logger($this->sharedState, $this->scope);
        $record = new LogRecord();
        $time = microtime(true) * LogRecord::NANOS_PER_SECOND;

        $this->processor->expects($this->once())->method('onEmit')
            ->with(
                $this->callback(function (ReadWriteLogRecord $record) use ($time) {
                    $this->assertNotNull($record->getObservedTimestamp());
                    $this->assertGreaterThan($time, $record->getObservedTimestamp());

                    return true;
                }),
                $this->anything(),
            );

        $logger->emit($record);
    }
}
