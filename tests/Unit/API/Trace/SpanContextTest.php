<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\API\Unit\Trace;

use OpenTelemetry\API\Trace as API;
use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\SpanContextValidator;
use OpenTelemetry\API\Trace\TraceState;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\API\Trace\SpanContext
 */
class SpanContextTest extends TestCase
{
    private const FIRST_TRACE_ID = '00000000000000000000000000000061';
    private const SECOND_TRACE_ID = '00000000000000300000000000000000';
    private const FIRST_SPAN_ID = '0000000000000061';
    private const SECOND_SPAN_ID = '3000000000000000';

    private API\SpanContextInterface $first;
    private API\SpanContextInterface $second;
    private API\SpanContextInterface $remote;

    protected function setUp(): void
    {
        $this->first = SpanContext::create(self::FIRST_TRACE_ID, self::FIRST_SPAN_ID, API\SpanContextInterface::TRACE_FLAG_DEFAULT, new TraceState('foo=bar'));
        $this->second = SpanContext::create(self::SECOND_TRACE_ID, self::SECOND_SPAN_ID, API\SpanContextInterface::TRACE_FLAG_SAMPLED, new TraceState('foo=baz'));
        $this->remote = SpanContext::createFromRemoteParent(self::SECOND_TRACE_ID, self::SECOND_SPAN_ID, API\SpanContextInterface::TRACE_FLAG_SAMPLED, new TraceState());
    }

    // region API

    public function test_is_valid(): void
    {
        $this->assertFalse(SpanContext::getInvalid()->isValid());

        $this->assertFalse(
            SpanContext::createFromRemoteParent(
                self::FIRST_TRACE_ID,
                SpanContextValidator::INVALID_SPAN
            )->isValid()
        );

        $this->assertFalse(
            SpanContext::createFromRemoteParent(
                SpanContextValidator::INVALID_TRACE,
                self::SECOND_SPAN_ID
            )->isValid()
        );

        $this->assertTrue($this->first->isValid());
        $this->assertTrue($this->second->isValid());
    }

    public function test_get_trace_id(): void
    {
        $this->assertSame(self::FIRST_TRACE_ID, $this->first->getTraceId());
        $this->assertSame(self::SECOND_TRACE_ID, $this->second->getTraceId());
    }

    public function test_get_trace_and_span_id_binary(): void
    {
        $traceId = '7d9df3359179cfd468fdf360f3e29f1a';
        $spanId = '7d9df3359179cfd4';

        $spanContext = SpanContext::create($traceId, $spanId);
        $this->assertSame(hex2bin($traceId), $spanContext->getTraceIdBinary());
        $this->assertSame(hex2bin($spanId), $spanContext->getSpanIdBinary());
    }

    public function test_get_span_id(): void
    {
        $this->assertSame(self::FIRST_SPAN_ID, $this->first->getSpanId());
        $this->assertSame(self::SECOND_SPAN_ID, $this->second->getSpanId());
    }

    public function test_get_trace_flags(): void
    {
        $this->assertSame(API\SpanContextInterface::TRACE_FLAG_DEFAULT, $this->first->getTraceFlags());
        $this->assertSame(API\SpanContextInterface::TRACE_FLAG_SAMPLED, $this->second->getTraceFlags());
    }

    public function test_get_trace_state(): void
    {
        $this->assertEquals(new TraceState('foo=bar'), $this->first->getTraceState());
        $this->assertEquals(new TraceState('foo=baz'), $this->second->getTraceState());
    }

    public function test_is_remote(): void
    {
        $this->assertFalse($this->first->isRemote());
        $this->assertFalse($this->second->isRemote());
        $this->assertTrue($this->remote->isRemote());
    }

    // endregion API
}
