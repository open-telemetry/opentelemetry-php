<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Logs;

use OpenTelemetry\API\Logs\LoggerInterface;
use OpenTelemetry\API\Logs\LogRecord;
use OpenTelemetry\API\Logs\Severity;
use OpenTelemetry\SDK\Logs\LogRecordBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LogRecordBuilder::class)]
class LogRecordBuilderTest extends TestCase
{
    private LoggerInterface $logger;
    private LogRecordBuilder $builder;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->builder = new LogRecordBuilder($this->logger);
    }

    public function test_set_timestamp_returns_self(): void
    {
        $result = $this->builder->setTimestamp(1234567890);

        $this->assertSame($this->builder, $result);
    }

    public function test_set_observed_timestamp_returns_self(): void
    {
        $result = $this->builder->setObservedTimestamp(1234567890);

        $this->assertSame($this->builder, $result);
    }

    public function test_set_context_returns_self(): void
    {
        $context = $this->createMock(\OpenTelemetry\Context\ContextInterface::class);
        $result = $this->builder->setContext($context);

        $this->assertSame($this->builder, $result);
    }

    public function test_set_context_with_false_uses_root_context(): void
    {
        $result = $this->builder->setContext(false);

        $this->assertSame($this->builder, $result);
    }

    public function test_set_context_with_null_returns_self(): void
    {
        $result = $this->builder->setContext(null);

        $this->assertSame($this->builder, $result);
    }

    public function test_set_severity_number_with_int_returns_self(): void
    {
        $result = $this->builder->setSeverityNumber(9);

        $this->assertSame($this->builder, $result);
    }

    public function test_set_severity_number_with_severity_enum_returns_self(): void
    {
        $result = $this->builder->setSeverityNumber(Severity::INFO);

        $this->assertSame($this->builder, $result);
    }

    public function test_set_severity_text_returns_self(): void
    {
        $result = $this->builder->setSeverityText('INFO');

        $this->assertSame($this->builder, $result);
    }

    public function test_set_body_returns_self(): void
    {
        $result = $this->builder->setBody('log message body');

        $this->assertSame($this->builder, $result);
    }

    public function test_set_attribute_returns_self(): void
    {
        $result = $this->builder->setAttribute('key', 'value');

        $this->assertSame($this->builder, $result);
    }

    public function test_set_attributes_returns_self(): void
    {
        $result = $this->builder->setAttributes(['key1' => 'value1', 'key2' => 'value2']);

        $this->assertSame($this->builder, $result);
    }

    public function test_set_exception_returns_self(): void
    {
        $exception = new \RuntimeException('test error');
        $result = $this->builder->setException($exception);

        $this->assertSame($this->builder, $result);
    }

    public function test_set_exception_sets_exception_attributes(): void
    {
        $exception = new \RuntimeException('test error');

        $this->logger->expects($this->once())
            ->method('emit')
            ->with($this->callback(function (LogRecord $logRecord) {
                $reflection = new \ReflectionProperty($logRecord, 'attributes');
                $attributes = $reflection->getValue($logRecord);

                return $attributes['exception.message'] === 'test error'
                    && $attributes['exception.type'] === \RuntimeException::class
                    && isset($attributes['exception.stacktrace']);
            }));

        $this->builder->setException($exception);
        $this->builder->emit();
    }

    public function test_set_event_name_returns_self(): void
    {
        $result = $this->builder->setEventName('my.event');

        $this->assertSame($this->builder, $result);
    }

    public function test_emit_calls_logger_emit(): void
    {
        $this->logger->expects($this->once())
            ->method('emit')
            ->with($this->isInstanceOf(LogRecord::class));

        $this->builder->emit();
    }

    public function test_fluent_interface(): void
    {
        $this->logger->expects($this->once())
            ->method('emit')
            ->with($this->isInstanceOf(LogRecord::class));

        $this->builder
            ->setTimestamp(1234567890)
            ->setObservedTimestamp(1234567891)
            ->setSeverityNumber(Severity::ERROR)
            ->setSeverityText('ERROR')
            ->setBody('something went wrong')
            ->setAttribute('key', 'value')
            ->setAttributes(['key2' => 'value2'])
            ->setEventName('my.event')
            ->emit();
    }
}
