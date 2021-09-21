<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Trace;

use OpenTelemetry\Sdk\Trace\SpanContext;
use OpenTelemetry\Sdk\Trace\TraceState;
use OpenTelemetry\Trace as API;
use PHPUnit\Framework\TestCase;

class SpanContextTest extends TestCase
{
    private const FIRST_TRACE_ID = '00000000000000000000000000000061';
    private const SECOND_TRACE_ID = '00000000000000300000000000000000';
    private const FIRST_SPAN_ID = '0000000000000061';
    private const SECOND_SPAN_ID = '3000000000000000';

    /** @var API\SpanContext */
    private $first;

    /** @var API\SpanContext */
    private $second;

    /** @var API\SpanContext */
    private $remote;

    protected function setUp(): void
    {
        $this->first = SpanContext::create(self::FIRST_TRACE_ID, self::FIRST_SPAN_ID, API\SpanContext::TRACE_FLAG_DEFAULT, new TraceState('foo=bar'));
        $this->second = SpanContext::create(self::SECOND_TRACE_ID, self::SECOND_SPAN_ID, API\SpanContext::TRACE_FLAG_SAMPLED, new TraceState('foo=baz'));
        $this->remote = SpanContext::createFromRemoteParent(self::SECOND_TRACE_ID, self::SECOND_SPAN_ID, API\SpanContext::TRACE_FLAG_SAMPLED, new TraceState());
    }

    public function test_isValid(): void
    {
        $this->assertFalse(SpanContext::getInvalid()->isValid());

        $this->assertFalse(
            SpanContext::create(
                self::FIRST_TRACE_ID,
                SpanContext::INVALID_SPAN,
            )->isValid()
        );

        $this->assertFalse(
            SpanContext::create(
                SpanContext::INVALID_TRACE,
                self::SECOND_SPAN_ID
            )->isValid()
        );

        $this->assertTrue($this->first->isValid());
        $this->assertTrue($this->second->isValid());
    }

    public function test_getTraceId(): void
    {
        $this->assertSame(self::FIRST_TRACE_ID, $this->first->getTraceId());
        $this->assertSame(self::SECOND_TRACE_ID, $this->second->getTraceId());
    }

    public function test_getSpanId(): void
    {
        $this->assertSame(self::FIRST_SPAN_ID, $this->first->getSpanId());
        $this->assertSame(self::SECOND_SPAN_ID, $this->second->getSpanId());
    }

    public function test_getTraceFlags(): void
    {
        $this->assertSame(API\SpanContext::TRACE_FLAG_DEFAULT, $this->first->getTraceFlags());
        $this->assertSame(API\SpanContext::TRACE_FLAG_SAMPLED, $this->second->getTraceFlags());
    }

    public function test_getTraceState(): void
    {
        $this->assertEquals(new TraceState('foo=bar'), $this->first->getTraceState());
        $this->assertEquals(new TraceState('foo=baz'), $this->second->getTraceState());
    }

    public function test_isRemote(): void
    {
        $this->assertFalse($this->first->isRemote());
        $this->assertFalse($this->second->isRemote());
        $this->assertTrue($this->remote->isRemote());
    }
}
