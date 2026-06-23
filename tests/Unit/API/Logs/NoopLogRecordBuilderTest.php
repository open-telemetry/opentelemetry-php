<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Logs;

use OpenTelemetry\API\Logs\NoopLogRecordBuilder;
use OpenTelemetry\API\Logs\Severity;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NoopLogRecordBuilder::class)]
class NoopLogRecordBuilderTest extends TestCase
{
    private NoopLogRecordBuilder $builder;

    #[\Override]
    protected function setUp(): void
    {
        $this->builder = new NoopLogRecordBuilder();
    }

    public function test_set_timestamp_returns_self(): void
    {
        $this->assertSame($this->builder, $this->builder->setTimestamp(123));
    }

    public function test_set_observed_timestamp_returns_self(): void
    {
        $this->assertSame($this->builder, $this->builder->setObservedTimestamp(123));
    }

    public function test_set_context_returns_self(): void
    {
        $this->assertSame($this->builder, $this->builder->setContext(null));
    }

    public function test_set_severity_number_returns_self(): void
    {
        $this->assertSame($this->builder, $this->builder->setSeverityNumber(Severity::INFO));
    }

    public function test_set_severity_text_returns_self(): void
    {
        $this->assertSame($this->builder, $this->builder->setSeverityText('INFO'));
    }

    public function test_set_body_returns_self(): void
    {
        $this->assertSame($this->builder, $this->builder->setBody('test'));
    }

    public function test_set_attribute_returns_self(): void
    {
        $this->assertSame($this->builder, $this->builder->setAttribute('key', 'value'));
    }

    public function test_set_attributes_returns_self(): void
    {
        $this->assertSame($this->builder, $this->builder->setAttributes([]));
    }

    public function test_set_exception_returns_self(): void
    {
        $this->assertSame($this->builder, $this->builder->setException(new \Exception('test')));
    }

    public function test_set_event_name_returns_self(): void
    {
        $this->assertSame($this->builder, $this->builder->setEventName('event'));
    }

    public function test_emit_does_nothing(): void
    {
        $this->builder->emit();
        $this->assertTrue(true); // just verifying no exception
    }
}
