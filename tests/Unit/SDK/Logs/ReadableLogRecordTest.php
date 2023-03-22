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
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Logs\ReadableLogRecord
 */
class ReadableLogRecordTest extends TestCase
{
    public function test_getters(): void
    {
        $scope = $this->createMock(InstrumentationScopeInterface::class);
        $sharedState = $this->createMock(LoggerSharedState::class);
        $context = $this->createMock(ContextInterface::class);
        $resource = $this->createMock(ResourceInfo::class);
        $limits = $this->createMock(LogRecordLimits::class);
        $attributeFactory = new AttributesFactory();
        $limits->method('getAttributeFactory')->willReturn($attributeFactory); //final
        $sharedState->method('getResource')->willReturn($resource);
        $sharedState->method('getLogRecordLimits')->willReturn($limits);
        $logRecord = (new LogRecord('body'))
            ->setSeverityNumber(5)
            ->setSeverityText('info')
            ->setTimestamp(11)
            ->setObservedTimestamp(22)
            ->setAttributes(['foo' => 'bar'])
            ->setContext($context);
        $record = new ReadableLogRecord($scope, $sharedState, $logRecord, true);

        $this->assertSame($scope, $record->getInstrumentationScope());
        $this->assertSame($resource, $record->getResource());
        $this->assertSame(11, $record->getTimestamp());
        $this->assertSame(22, $record->getObservedTimestamp());
        $this->assertSame($context, $record->getContext());
        $this->assertSame(5, $record->getSeverityNumber());
        $this->assertSame('info', $record->getSeverityText());
        $this->assertSame('body', $record->getBody());
        $this->assertEquals(['foo' => 'bar'], $record->getAttributes()->toArray());
    }
}
