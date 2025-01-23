<?php

declare(strict_types=1);

namespace Unit\SDK\Logs;

use OpenTelemetry\API\Logs\LogRecord;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Logs\LoggerSharedState;
use OpenTelemetry\SDK\Logs\LogRecordLimitsBuilder;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;
use OpenTelemetry\SDK\Logs\ReadWriteLogRecord;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ReadWriteLogRecord::class)]
class ReadWriteLogRecordTest extends TestCase
{
    private ReadWriteLogRecord $record;

    public function setUp(): void
    {
        $limits = (new LogRecordLimitsBuilder())->setAttributeCountLimit(10)->build();
        $loggerSharedState = new LoggerSharedState(
            ResourceInfoFactory::emptyResource(),
            $limits,
            $this->createMock(LogRecordProcessorInterface::class)
        );
        $record = (new LogRecord())
            ->setTimestamp(1)
            ->setObservedTimestamp(2)
            ->setSeverityText('severity')
            ->setSeverityNumber(3)
            ->setBody('body')
            ->setAttributes(['key' => 'value']);

        $this->record = new ReadWriteLogRecord(
            $this->createMock(InstrumentationScopeInterface::class),
            $loggerSharedState,
            $record
        );
    }

    public function test_modify_timestamp(): void
    {
        $this->record->setTimestamp(4);
        $this->assertEquals(4, $this->record->getTimestamp());
    }

    public function test_set_observed_timestamp(): void
    {
        $this->record->setObservedTimestamp(5);
        $this->assertEquals(5, $this->record->getObservedTimestamp());
    }

    public function test_set_severity_text(): void
    {
        $this->record->setSeverityText('severity2');
        $this->assertEquals('severity2', $this->record->getSeverityText());
    }

    public function test_set_severity_number(): void
    {
        $this->record->setSeverityNumber(6);
        $this->assertEquals(6, $this->record->getSeverityNumber());
    }

    public function test_set_body(): void
    {
        $this->record->setBody('body2');
        $this->assertEquals('body2', $this->record->getBody());
    }

    public function test_add_attribute(): void
    {
        $this->record->setAttribute('key2', 'value2');
        $this->assertEquals(['key' => 'value', 'key2' => 'value2'], $this->record->getAttributes()->toArray());
    }

    public function test_remove_attribute(): void
    {
        $this->record->removeAttribute('key');
        $this->assertEquals([], $this->record->getAttributes()->toArray());
    }

    public function test_modify_attribute(): void
    {
        $this->record->setAttribute('key', 'updated');
        $this->assertEquals(['key' => 'updated'], $this->record->getAttributes()->toArray());
    }
}
