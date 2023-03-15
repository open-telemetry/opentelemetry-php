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
use Psr\Log\LogLevel;

/**
 * @covers \OpenTelemetry\SDK\Logs\Logger
 * @psalm-suppress UndefinedInterfaceMethod
 */
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
        $logger = new Logger($this->sharedState, $this->scope, true);
        $record = $this->createMock(LogRecord::class);

        $this->processor->expects($this->once())->method('onEmit')
            ->with(
                $this->isInstanceOf(ReadWriteLogRecord::class),
                $this->isInstanceOf(ContextInterface::class)
            );

        $logger->logRecord($record);
    }

    /**
     * @dataProvider psr3LevelProvider
     */
    public function test_psr3_methods(string $level): void
    {
        $logger = new Logger($this->sharedState, $this->scope, true);

        $this->processor->expects($this->once())->method('onEmit')->with($this->callback(function (ReadWriteLogRecord $record) use ($level) {
            $this->assertSame($level, $record->getSeverityText());

            return true;
        }));

        $logger->{$level}('foo', ['bar' => 'baz']);
    }

    public static function psr3LevelProvider(): array
    {
        return [
            [LogLevel::EMERGENCY],
            [LogLevel::DEBUG],
            [LogLevel::ALERT],
            [LogLevel::CRITICAL],
            [LogLevel::INFO],
            [LogLevel::ERROR],
            [LogLevel::WARNING],
            [LogLevel::NOTICE],
        ];
    }
}