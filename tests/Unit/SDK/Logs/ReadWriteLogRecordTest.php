<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Logs;

use OpenTelemetry\API\Logs\LogRecord;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Logs\LoggerSharedState;
use OpenTelemetry\SDK\Logs\LogRecordLimitsBuilder;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;
use OpenTelemetry\SDK\Logs\ReadableLogRecord;
use OpenTelemetry\SDK\Logs\ReadWriteLogRecord;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.61.0/specification/logs/sdk.md#readwritelogrecord
 */
#[CoversClass(ReadWriteLogRecord::class)]
#[CoversClass(ReadableLogRecord::class)]
#[Group('logs-compliance')]
class ReadWriteLogRecordTest extends TestCase
{
    private ReadWriteLogRecord $record;

    #[\Override]
    public function setUp(): void
    {
        $this->record = $this->createRecord(
            (new LogRecord('body'))
                ->setTimestamp(1)
                ->setObservedTimestamp(2)
                ->setSeverityText('severity')
                ->setSeverityNumber(3)
                ->setEventName('event')
                ->setAttributes(['key' => 'value']),
        );
    }

    public function test_set_timestamp(): void
    {
        $this->record->setTimestamp(4);
        $this->assertSame(4, $this->record->getTimestamp());
    }

    public function test_set_observed_timestamp(): void
    {
        $this->record->setObservedTimestamp(5);
        $this->assertSame(5, $this->record->getObservedTimestamp());
    }

    public function test_set_severity_text(): void
    {
        $this->record->setSeverityText('severity2');
        $this->assertSame('severity2', $this->record->getSeverityText());
    }

    public function test_set_severity_number(): void
    {
        $this->record->setSeverityNumber(6);
        $this->assertSame(6, $this->record->getSeverityNumber());
    }

    public function test_set_body(): void
    {
        $this->record->setBody('body2');
        $this->assertSame('body2', $this->record->getBody());
    }

    public function test_set_event_name(): void
    {
        $this->record->setEventName('event2');
        $this->assertSame('event2', $this->record->getEventName());
    }

    public function test_add_attribute(): void
    {
        $this->record->setAttribute('key2', 'value2');
        $this->assertSame(['key' => 'value', 'key2' => 'value2'], $this->record->getAttributes()->toArray());
    }

    public function test_add_attributes(): void
    {
        $this->record->setAttributes(['key2' => 'value2', 'key3' => 'value3']);
        $this->assertSame(['key' => 'value', 'key2' => 'value2', 'key3' => 'value3'], $this->record->getAttributes()->toArray());
    }

    public function test_modify_attribute(): void
    {
        $this->record->setAttribute('key', 'updated');
        $this->assertSame(['key' => 'updated'], $this->record->getAttributes()->toArray());
    }

    public function test_remove_attribute(): void
    {
        $this->record->removeAttribute('key');
        $this->assertSame([], $this->record->getAttributes()->toArray());
    }

    public function test_added_attributes_respect_limits(): void
    {
        $record = $this->createRecord((new LogRecord())->setAttribute('one', 'one'), attributeCountLimit: 1);

        $record->setAttribute('two', 'two');

        $this->assertSame(['one' => 'one'], $record->getAttributes()->toArray());
        $this->assertSame(1, $record->getAttributes()->getDroppedAttributesCount());
    }

    private function createRecord(LogRecord $logRecord, ?int $attributeCountLimit = null): ReadWriteLogRecord
    {
        $limits = new LogRecordLimitsBuilder();
        if ($attributeCountLimit !== null) {
            $limits->setAttributeCountLimit($attributeCountLimit);
        }

        return new ReadWriteLogRecord(
            $this->createStub(InstrumentationScopeInterface::class),
            new LoggerSharedState(
                ResourceInfoFactory::emptyResource(),
                $limits->build(),
                $this->createStub(LogRecordProcessorInterface::class),
            ),
            $logRecord,
        );
    }
}
