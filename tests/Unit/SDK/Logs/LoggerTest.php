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
 * @covers \OpenTelemetry\SDK\Logs\Logger
 */
class LoggerTest extends TestCase
{
    public function test_log_record(): void
    {
        $sharedState = $this->createMock(LoggerSharedState::class);
        $processor = $this->createMock(LogRecordProcessorInterface::class);
        $sharedState->method('getProcessor')->willReturn($processor);
        $scope = new InstrumentationScope('foo', '1.0', 'schema.url', Attributes::create([])); //final
        $includeTraceContext = true;
        $logger = new Logger($sharedState, $scope, $includeTraceContext);
        $record = $this->createMock(LogRecord::class);

        $processor->expects($this->once())->method('onEmit')
            ->with(
                $this->isInstanceOf(ReadWriteLogRecord::class),
                $this->isInstanceOf(ContextInterface::class)
            );

        $logger->logRecord($record);
    }
}
