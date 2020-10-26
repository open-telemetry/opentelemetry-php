<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Trace;

use function iterator_to_array;
use OpenTelemetry\Sdk\Resource\ResourceInfo;
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
        $tracerProvider = new SDK\TracerProvider();
        $tracer = new Tracer($tracerProvider, ResourceInfo::create(new Attributes([])));
        $tracer->startAndActivateSpan('tracer1.firstSpan');
        $spanContext = $tracer->getActiveSpan()->getContext();

        $spanContext2 = SpanContext::restore($spanContext->getTraceId(), $spanContext->getSpanId());
        $tracer2 = new Tracer($tracerProvider, ResourceInfo::create(new Attributes([])), $spanContext2);
        $tracer2->startAndActivateSpan('tracer2.firstSpan');

        $this->assertSame(
            $tracer->getActiveSpan()->getContext()->getTraceId(),
            $tracer2->getActiveSpan()->getContext()->getTraceId()
        );
    }

    public function testSpanNameUpdate()
    {
        $tracerProvider = new SDK\TracerProvider();
        $tracer = $tracerProvider->getTracer('OpenTelemetry.TracingTest');
        $database = $tracer->startAndActivateSpan('database');
        $this->assertSame($database->getSpanName(), 'database');
        $database->updateName('tarantool');
        $this->assertSame($database->getSpanName(), 'tarantool');
    }

    public function testNestedSpans()
    {
        $tracerProvider = new SDK\TracerProvider();
        $tracer = $tracerProvider->getTracer('OpenTelemetry.TracingTest');

        $guard = $tracer->startAndActivateSpan('guard.validate');
        $connection = $tracer->startAndActivateSpan('guard.validate.connection');
        $procedure = $tracer->startAndActivateSpan('guard.procedure.registration')->end();
        $connection->end();
        $policy = $tracer->startAndActivateSpan('policy.describe')->end();

        $guard->end();

        $this->assertEquals($connection->getParent(), $guard->getContext());
        $this->assertEquals($procedure->getParent(), $connection->getContext());
        $this->assertEquals($policy->getParent(), $guard->getContext());

        $this->assertCount(4, $tracer->getSpans());
    }

    public function testCreateSpan()
    {
        $tracerProvider = new SDK\TracerProvider();
        $tracer = $tracerProvider->getTracer('OpenTelemetry.TracingTest');
        $tracer->startAndActivateSpan('firstSpan');
        $global = $tracer->getActiveSpan();

        $mysql = $tracer->startAndActivateSpan('mysql');
        $this->assertSame($tracer->getActiveSpan(), $mysql);
        $this->assertSame($global->getContext()->getTraceId(), $mysql->getContext()->getTraceId());
        $this->assertEquals($mysql->getParent(), $global->getContext());
        $this->assertNotNull($mysql->getStartEpochTimestamp());
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
    }

    public function testStatusManipulation()
    {
        $tracerProvider = new SDK\TracerProvider();
        $tracer = $tracerProvider->getTracer('OpenTelemetry.TracingTest');

        $cancelled = $tracer->startAndActivateSpan('cancelled')
            ->setSpanStatus(SpanStatus::CANCELLED)
            ->end();
        self::assertFalse($cancelled->isStatusOK());
        self::assertSame(SpanStatus::CANCELLED, $cancelled->getCanonicalStatusCode());
        self::assertSame(SpanStatus::DESCRIPTION[SpanStatus::CANCELLED], $cancelled->getStatusDescription());
        self::assertCount(1, $tracer->getSpans());
    }

    public function testSetSpanStatusWhenNotRecording()
    {
        $tracerProvider = new SDK\TracerProvider();
        $tracer = $tracerProvider->getTracer('OpenTelemetry.TracingTest');

        $span = $tracer->startAndActivateSpan('span')
            ->setSpanStatus(SpanStatus::UNKNOWN, 'my description')
            ->end()
            ->setSpanStatus(SpanStatus::CANCELLED, 'nope');

        $this->assertEquals(SpanStatus::new(SpanStatus::UNKNOWN, 'my description'), $span->getStatus());
    }

    public function testSpanAttributesApi()
    {
        $tracerProvider = new SDK\TracerProvider();
        $tracer = $tracerProvider->getTracer('OpenTelemetry.TracingTest');
        $tracer->startAndActivateSpan('firstSpan');
        /**
         * @var SDK\Span
         */
        $span = $tracer->getActiveSpan();

        self::assertInstanceOf(SDK\Span::class, $span);

        // set attributes
        $span->replaceAttributes(new Attributes(['username' => 'nekufa']));

        // get attribute
        $this->assertEquals(new Attribute('username', 'nekufa'), $span->getAttribute('username'));

        // otherwrite
        $span->replaceAttributes(new Attributes(['email' => 'nekufa@gmail.com']));

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

        // attribute key - code coverage
        self::assertEquals('email', $span->getAttribute('email')->getKey());
        self::assertEquals('username', $span->getAttribute('username')->getKey());

        // keep order
        $expected = [
            'a' => new Attribute('a', 1),
            'b' => new Attribute('b', 2),
        ];
        $span->replaceAttributes(new Attributes(['a' => 1, 'b' => 2]));

        $actual = iterator_to_array($span->getAttributes());
        self::assertEquals($expected, $actual);

        // attribute update don't change the order
        $span->setAttribute('a', 3);
        $span->setAttribute('b', 4);

        $expected = [
            'a' => new Attribute('a', 3),
            'b' => new Attribute('b', 4),
        ];
        $actual = iterator_to_array($span->getAttributes());
        self::assertEquals($expected, $actual);
    }

    public function testSetAttributeReplaceAttributesWhenNotRecording()
    {
        $tracer = (new SDK\TracerProvider())->getTracer('OpenTelemetry.TracingTest');
        $span = $tracer->startAndActivateSpan('testSpan');
        $span->setAttribute('key1', 'value1');
        $span->end();

        $this->assertFalse($span->isRecording());
        $this->assertCount(1, $span->getAttributes());
        $this->assertArrayHasKey('key1', iterator_to_array($span->getAttributes()));

        $span->setAttribute('key2', 'value2');
        $this->assertCount(1, $span->getAttributes());
        $this->assertArrayHasKey('key1', iterator_to_array($span->getAttributes()));
        $this->assertArrayNotHasKey('key2', iterator_to_array($span->getAttributes()));

        $span->replaceAttributes(new Attributes(['foo' => 'bar']));
        $this->assertCount(1, $span->getAttributes());
        $this->assertArrayHasKey('key1', iterator_to_array($span->getAttributes()));
        $this->assertArrayNotHasKey('foo', iterator_to_array($span->getAttributes()));
    }

    public function testEventRegistration()
    {
        $tracerProvider = new SDK\TracerProvider();
        $tracer = $tracerProvider->getTracer('OpenTelemetry.TracingTest');
        $span = $tracer->startAndActivateSpan('database');
        $eventAttributes = new Attributes([
            'space' => 'guard.session',
            'id' => 67235,
        ]);
        $timestamp = Clock::get()->timestamp();
        $span->addEvent('select', $timestamp, $eventAttributes);

        $events = $span->getEvents();
        self::assertCount(1, $events);

        [$event] = iterator_to_array($events);
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

    public function testAddEventWhenNotRecording()
    {
        $tracerProvider = new SDK\TracerProvider();
        $tracer = $tracerProvider->getTracer('OpenTelemetry.TracingTest');
        $span = $tracer->startAndActivateSpan('span');
        $span->addEvent('recorded_event', 0);

        $events = $span->getEvents();
        self::assertCount(1, $events);

        [$event] = iterator_to_array($events);
        $this->assertSame($event->getName(), 'recorded_event');

        $span->end();
        $span->addEvent('not_recorded_event', 1);

        $this->assertCount(1, $span->getEvents());
        [$event] = iterator_to_array($events);
        $this->assertSame($event->getName(), 'recorded_event');
    }

    public function testStopRecordingWhenSpanEnds()
    {
        $tracerProvider = new SDK\TracerProvider();
        $tracer = $tracerProvider->getTracer('OpenTelemetry.TracingTest');
        $span = $tracer->startAndActivateSpan('span');
        $this->assertTrue($span->isRecording());
        $span->end();
        $this->assertFalse($span->isRecording());
    }

    public function testParentSpanContext()
    {
        $tracerProvider = new SDK\TracerProvider();
        $tracer = $tracerProvider->getTracer('OpenTelemetry.TracingTest');
        $tracer->startAndActivateSpan('firstSpan');
        $global = $tracer->getActiveSpan();
        $request = $tracer->startAndActivateSpan('request');
        $this->assertSame($request->getParent()->getSpanId(), $global->getContext()->getSpanId());
        $this->assertNull($global->getParent());
        $this->assertNotNull($request->getParent());
    }

    public function testActiveRootSpanIsNoopSpanIfNoParentProvided()
    {
        $tracer = (new SDK\TracerProvider())->getTracer('OpenTelemetry.TracingTest');

        $this->assertInstanceOf(
            SDK\NoopSpan::class,
            $tracer->getActiveSpan()
        );
    }
}
