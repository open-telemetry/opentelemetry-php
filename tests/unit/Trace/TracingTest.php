<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Tests;

use OpenTelemetry\Sdk\Trace as SDK;
use OpenTelemetry\Sdk\Trace\Attribute;
use OpenTelemetry\Sdk\Trace\Attributes;
use OpenTelemetry\Sdk\Trace\Clock;
use OpenTelemetry\Sdk\Trace\SpanContext;
use OpenTelemetry\Sdk\Trace\SpanStatus;
use OpenTelemetry\Sdk\Trace\Tracer;
use PHPUnit\Framework\TestCase;

class TracingTest extends TestCase
{
    public function testContextGenerationAndRestore()
    {
        $spanContext = SpanContext::generate();
        $this->assertSame(strlen($spanContext->getTraceId()), 32);
        $this->assertSame(strlen($spanContext->getSpanId()), 16);

        $spanContext2 = SpanContext::generate();
        $this->assertNotSame($spanContext->getTraceId(), $spanContext2->getTraceId());
        $this->assertNotSame($spanContext->getSpanId(), $spanContext2->getSpanId());

        $spanContext3 = SpanContext::restore($spanContext->getTraceId(), $spanContext->getSpanId());
        $this->assertSame($spanContext3->getTraceId(), $spanContext->getTraceId());
        $this->assertSame($spanContext3->getSpanId(), $spanContext->getSpanId());
    }

    public function testTracerSpanContextRestore()
    {
        $tracer = new Tracer();
        $spanContext = $tracer->getActiveSpan()->getContext();

        $spanContext2 = SpanContext::restore($spanContext->getTraceId(), $spanContext->getSpanId());
        $tracer2 = new Tracer([], $spanContext2);

        $this->assertSame($tracer->getActiveSpan()->getContext()->getTraceId(), $tracer2->getActiveSpan()->getContext()->getTraceId());
    }

    public function testSpanNameUpdate()
    {
        $database = (new Tracer())->startAndActivateSpan('database');
        $this->assertSame($database->getSpanName(), 'database');
        $database->updateName('tarantool');
        $this->assertSame($database->getSpanName(), 'tarantool');
    }

    public function testNestedSpans()
    {
        $tracer = new Tracer();

        $guard = $tracer->startAndActivateSpan('guard.validate');
        $connection = $tracer->startAndActivateSpan('guard.validate.connection');
        $procedure = $tracer->startAndActivateSpan('guard.procedure.registration')->end();
        $connection->end();
        $policy = $tracer->startAndActivateSpan('policy.describe')->end();

        $guard->end();

        $this->assertEquals($connection->getParent(), $guard->getContext());
        $this->assertEquals($procedure->getParent(), $connection->getContext());
        $this->assertEquals($policy->getParent(), $guard->getContext());

        $this->assertCount(5, $tracer->getSpans());
    }

    public function testCreateSpan()
    {
        $tracer = new Tracer();
        $global = $tracer->getActiveSpan();

        $mysql = $tracer->startAndActivateSpan('mysql');
        $this->assertSame($tracer->getActiveSpan(), $mysql);
        $this->assertSame($global->getContext()->getTraceId(), $mysql->getContext()->getTraceId());
        $this->assertEquals($mysql->getParent(), $global->getContext());
        $this->assertNotNull($mysql->getStartTimestamp());
        $this->assertTrue($mysql->isRecording());
        $this->assertNull($mysql->getDuration());

        $mysql->end();
        $this->assertFalse($mysql->isRecording());
        $this->assertNotNull($mysql->getDuration());

        $duration = $mysql->getDuration();

        // subsequent calls to end should be ignored
        $mysql->end();
        self::assertSame($duration, $mysql->getDuration());

        self::assertTrue($mysql->isStatusOK());
        
        // active span rolled back
        $this->assertSame($tracer->getActiveSpan(), $global);
        
        // active span should be kept for global span
        $global->end();
        $this->assertSame($tracer->getActiveSpan(), $global);
        self::assertTrue($global->isStatusOK());
    }

    public function testStatusManipulation()
    {
        $tracer = new Tracer();

        $cancelled = $tracer->startAndActivateSpan('cancelled')
            ->setSpanStatus(SpanStatus::CANCELLED)
            ->end();
        self::assertFalse($cancelled->isStatusOK());
        self::assertSame(SpanStatus::CANCELLED, $cancelled->getCanonicalStatusCode());
        self::assertSame(SpanStatus::DESCRIPTION[SpanStatus::CANCELLED], $cancelled->getStatusDescription());

        // todo: hold up, _two_?
        self::assertCount(2, $tracer->getSpans());
    }

    public function testSpanAttributesApi()
    {
        /**
         * @var SDK\Span
         */
        $span = (new Tracer())->getActiveSpan();

        self::assertInstanceOf(SDK\Span::class, $span);

        // set attributes
        $span->replaceAttributes(['username' => 'nekufa']);

        // get attribute
        $this->assertEquals(new Attribute('username', 'nekufa'), $span->getAttribute('username'));

        // otherwrite
        $span->replaceAttributes(['email' => 'nekufa@gmail.com',]);

        // null attributes
        self::assertNull($span->getAttribute('username'));
        self::assertEquals(new Attribute('email', 'nekufa@gmail.com'), $span->getAttribute('email'));

        // set attribute
        $span->setAttribute('username', 'nekufa');
        self::assertEquals(new Attribute('username', 'nekufa'), $span->getAttribute('username'));
        $attributes = $span->getAttributes();
        self::assertCount(2, $attributes);
        self::assertEquals(new Attribute('email', 'nekufa@gmail.com'), $span->getAttribute('email'));
        self::assertEquals(new Attribute('username', 'nekufa'), $span->getAttribute('username'));

        // keep order
        $expected = [
            'a' => new Attribute('a', 1),
            'b' => new Attribute('b', 2),
        ];
        $span->replaceAttributes(['a' => 1, 'b' => 2]);

        $actual = \iterator_to_array($span->getAttributes());
        self::assertEquals($expected, $actual);

        // attribute update don't change the order
        $span->setAttribute('a', 3);
        $span->setAttribute('b', 4);

        $expected = [
            'a' => new Attribute('a', 3),
            'b' => new Attribute('b', 4),
        ];
        $actual = \iterator_to_array($span->getAttributes());
        self::assertEquals($expected, $actual);
    }

    public function testSetAttributeWhenNotRecording()
    {
        // todo: implement test
        $this->markTestIncomplete();
    }

    public function testEventRegistration()
    {
        $span = (new Tracer())->startAndActivateSpan('database');
        $eventAttributes = new Attributes([
            'space' => 'guard.session',
            'id' => 67235,
        ]);
        $timestamp = Clock::get()->timestamp();
        $span->addEvent('select', $timestamp, $eventAttributes);

        $events = $span->getEvents();
        self::assertCount(1, $events);

        [$event] = \iterator_to_array($events);
        $this->assertSame($event->getName(), 'select');
        $attributes = new Attributes([
            'space' => 'guard.session',
            'id' => 67235,
        ]);
        self::assertEquals($attributes, $event->getAttributes());

        $span->addEvent('update', $timestamp)
                    ->setAttribute('space', 'guard.session')
                    ->setAttribute('id', 67235)
                    ->setAttribute('active_at', time());

        $this->assertCount(2, $span->getEvents());
    }

    public function testBuilder()
    {
        $spanContext = SpanContext::generate();
        $tracer = new Tracer([], $spanContext);

        $this->assertInstanceOf(Tracer::class, $tracer);
        $this->assertEquals($tracer->getActiveSpan()->getContext(), $spanContext);
    }

    public function testParentSpanContext()
    {
        $tracer = new Tracer();
        $global = $tracer->getActiveSpan();
        $request = $tracer->startAndActivateSpan('request');
        $this->assertSame($request->getParent()->getSpanId(), $global->getContext()->getSpanId());
        $this->assertNull($global->getParent());
        $this->assertNotNull($request->getParent());
    }
}
