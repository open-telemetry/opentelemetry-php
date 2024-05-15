<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Logs;

use OpenTelemetry\API\Logs\LogRecord;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesFactory;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Logs\LoggerSharedState;
use OpenTelemetry\SDK\Logs\LogRecordLimits;
use OpenTelemetry\SDK\Logs\ReadableLogRecord;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(ReadableLogRecord::class)]
class ReadableLogRecordTest extends TestCase
{
    private InstrumentationScopeInterface $scope;
    private ContextInterface $context;
    private LoggerSharedState $sharedState;
    private ResourceInfo $resource;
    public function setUp(): void
    {
        $this->scope = $this->createMock(InstrumentationScopeInterface::class);
        $this->sharedState = $this->createMock(LoggerSharedState::class);
        $this->context = $this->createMock(ContextInterface::class);
        $this->resource = $this->createMock(ResourceInfo::class);
        $limits = $this->createMock(LogRecordLimits::class);
        $attributeFactory = new AttributesFactory();
        $limits->method('getAttributeFactory')->willReturn($attributeFactory); //final
        $this->sharedState->method('getResource')->willReturn($this->resource);
        $this->sharedState->method('getLogRecordLimits')->willReturn($limits);
    }

    public function test_getters(): void
    {
        $logRecord = (new LogRecord('body'))
            ->setSeverityNumber(5)
            ->setSeverityText('info')
            ->setTimestamp(11)
            ->setObservedTimestamp(22)
            ->setAttributes(['foo' => 'bar'])
            ->setContext($this->context);
        $record = new ReadableLogRecord($this->scope, $this->sharedState, $logRecord);

        $this->assertSame($this->scope, $record->getInstrumentationScope());
        $this->assertSame($this->resource, $record->getResource());
        $this->assertSame(11, $record->getTimestamp());
        $this->assertSame(22, $record->getObservedTimestamp());
        $this->assertSame($this->context, $record->getContext());
        $this->assertSame(5, $record->getSeverityNumber());
        $this->assertSame('info', $record->getSeverityText());
        $this->assertSame('body', $record->getBody());
        $this->assertEquals(['foo' => 'bar'], $record->getAttributes()->toArray());
    }

    #[Group('logs-compliance')]
    public function test_log_record_can_accept_complex_attributes(): void
    {
        $homogeneous = [1,2,3,4,5];
        $heterogeneous = ['one', 2, 3.14, true];
        $complex = ['foo' => ['bar' => 'baz', 'bat' => 3.14]];
        $logRecord = (new LogRecord())
            ->setAttribute('homogeneous', $homogeneous)
            ->setAttribute('heterogeneous', $heterogeneous)
            ->setAttribute('complex', $complex);

        $record = new ReadableLogRecord($this->scope, $this->sharedState, $logRecord);
        $this->assertSame(0, $record->getAttributes()->getDroppedAttributesCount());
        $this->assertSame($complex, $record->getAttributes()->get('complex'));
        $this->assertSame($homogeneous, $record->getAttributes()->get('homogeneous'));
        $this->assertSame($heterogeneous, $record->getAttributes()->get('heterogeneous'));
    }
}
