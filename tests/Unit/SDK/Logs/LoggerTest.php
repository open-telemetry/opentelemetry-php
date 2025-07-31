<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Logs;

use OpenTelemetry\API\Behavior\Internal\Logging;
use OpenTelemetry\API\Behavior\Internal\LogWriter\LogWriterInterface;
use OpenTelemetry\API\Logs\LogRecord;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScope;
use OpenTelemetry\SDK\Common\InstrumentationScope\Configurator;
use OpenTelemetry\SDK\Logs\Logger;
use OpenTelemetry\SDK\Logs\LoggerConfig;
use OpenTelemetry\SDK\Logs\LoggerSharedState;
use OpenTelemetry\SDK\Logs\LogRecordLimitsBuilder;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;
use OpenTelemetry\SDK\Logs\ReadWriteLogRecord;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @psalm-suppress UndefinedInterfaceMethod
 */
#[CoversClass(Logger::class)]
class LoggerTest extends TestCase
{
    /** @var LogWriterInterface&MockObject $logWriter */
    private LogWriterInterface $logWriter;
    private LoggerSharedState $sharedState;
    private LogRecordProcessorInterface $processor;
    private InstrumentationScope $scope;

    #[\Override]
    public function setUp(): void
    {
        $limits = (new LogRecordLimitsBuilder())->setAttributeCountLimit(1)->build();
        $this->sharedState = $this->createMock(LoggerSharedState::class);
        $this->sharedState->method('getLogRecordLimits')->willReturn($limits);
        $this->processor = $this->createMock(LogRecordProcessorInterface::class);
        $this->sharedState->method('getProcessor')->willReturn($this->processor);
        $this->scope = new InstrumentationScope('foo', '1.0', 'schema.url', Attributes::create([])); //final

        $this->logWriter = $this->createMock(LogWriterInterface::class);
        Logging::setLogWriter($this->logWriter);
    }

    #[\Override]
    public function tearDown(): void
    {
        Logging::reset();
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
        $time = microtime(true) * (float) LogRecord::NANOS_PER_SECOND;

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

    public function test_logs_dropped_attributes(): void
    {
        $this->logWriter
            ->expects($this->once())
            ->method('write')
            ->with(
                $this->equalTo('warning'),
                $this->stringContains('Dropped'),
                $this->callback(function (array $context): bool {
                    $this->assertArrayHasKey('attributes', $context);
                    $this->assertSame(2, $context['attributes']);

                    return true;
                }),
            );
        $logger = new Logger($this->sharedState, $this->scope);
        $record = new LogRecord();
        //limit is 1
        $record->setAttributes([
            'one' => 'attr_one',
            'two' => 'attr_two',
            'three' => 'attr_three',
        ]);

        $logger->emit($record);
    }

    public function test_enabled(): void
    {
        $logger = new Logger($this->sharedState, $this->scope);
        $this->assertTrue($logger->isEnabled());

        $this->processor->expects($this->once())->method('onEmit');

        $logger->emit(new LogRecord());
    }

    public function test_does_not_log_if_disabled(): void
    {
        $configurator = Configurator::logger()->with(static fn (LoggerConfig $config) => $config->setDisabled(true), name: 'foo');
        $logger = new Logger($this->sharedState, $this->scope, $configurator);
        $this->assertFalse($logger->isEnabled());

        $this->processor->expects($this->never())->method('onEmit');

        $logger->emit(new LogRecord());
    }
}
