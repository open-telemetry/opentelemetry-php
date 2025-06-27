<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Contrib\Otlp;

use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\Contrib\Otlp\LogsConverter;
use OpenTelemetry\SDK\Logs\ReadableLogRecord;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(LogsConverter::class)]
class LogsConverterTest extends TestCase
{
    private const TRACE_ID_BASE16 = 'ff000000000000000000000000000041';
    private const SPAN_ID_BASE16 = 'ff00000000000041';
    private const FLAGS = 12;
    private ReadableLogRecord&MockObject $record;
    private LogsConverter $converter;

    public function setUp(): void
    {
        $this->converter = new LogsConverter();
        $this->record = $this->createMock(ReadableLogRecord::class);
    }

    public function test_convert(): void
    {
        $this->record->method('getBody')->willReturn('body');

        $converted = $this->converter->convert([$this->record]);
        /** @psalm-suppress InvalidArgument */
        $logRecord = $converted->getResourceLogs()[0]->getScopeLogs()[0]->getLogRecords()[0];
        $this->assertSame('body', $logRecord->getBody()->getStringValue());
    }

    public function test_convert_event_name(): void
    {
        $this->record->method('getEventName')->willReturn('my.event');

        $converted = $this->converter->convert([$this->record]);
        /** @psalm-suppress InvalidArgument */
        $logRecord = $converted->getResourceLogs()[0]->getScopeLogs()[0]->getLogRecords()[0];

        $this->assertSame('my.event', $logRecord->getEventName());
    }

    public function test_convert_with_context(): void
    {
        $spanContext = SpanContext::create(self::TRACE_ID_BASE16, self::SPAN_ID_BASE16, self::FLAGS);
        $span = $this->createMock(SpanInterface::class);
        $span->method('getContext')->willReturn($spanContext);
        $this->record->method('getSpanContext')->willReturn($spanContext);
        $request = $this->converter->convert([$this->record]);
        /** @psalm-suppress InvalidArgument */
        $row = $request->getResourceLogs()[0]->getScopeLogs()[0]->getLogRecords()[0];
        $this->assertSame(self::TRACE_ID_BASE16, bin2hex((string) $row->getTraceId()));
        $this->assertSame(self::SPAN_ID_BASE16, bin2hex((string) $row->getSpanId()));
        $this->assertSame(self::FLAGS, $row->getFlags());
    }
}
