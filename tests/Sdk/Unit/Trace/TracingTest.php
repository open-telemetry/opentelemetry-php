<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Trace;

use Exception;
use function iterator_to_array;
use OpenTelemetry\Sdk\InstrumentationLibrary;
use OpenTelemetry\Sdk\Resource\ResourceConstants;
use OpenTelemetry\Sdk\Resource\ResourceInfo;
use OpenTelemetry\Sdk\Trace as SDK;
use OpenTelemetry\Sdk\Trace\Attribute;
use OpenTelemetry\Sdk\Trace\Attributes;
use OpenTelemetry\Sdk\Trace\Clock;
use OpenTelemetry\Sdk\Trace\Span;
use OpenTelemetry\Sdk\Trace\SpanContext;
use OpenTelemetry\Sdk\Trace\SpanStatus;
use OpenTelemetry\Sdk\Trace\Tracer;
use OpenTelemetry\Sdk\Trace\TracerProvider;
use OpenTelemetry\Trace as API;
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
        $tracerProvider = new SDK\TracerProvider();
        $tracer = new Tracer($tracerProvider, new InstrumentationLibrary('OpenTelemetry.TracingTest'), ResourceInfo::create(new Attributes([])));
        $tracer->startAndActivateSpan('tracer1.firstSpan');
        $spanContext = $tracer->getActiveSpan()->getContext();

        $spanContext2 = SpanContext::restore($spanContext->getTraceId(), $spanContext->getSpanId());
        $tracer2 = new Tracer($tracerProvider, new InstrumentationLibrary('OpenTelemetry.TracingTest'), ResourceInfo::create(new Attributes([])), $spanContext2);
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
        /** @var Tracer $tracer */
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

    public function testGetStackTrace()
    {
        $stacktrace = 'Exception: Thrown from here
 at OpenTelemetry.Tests.Sdk.Unit.Trace.TracingTest.testGetStackTrace(TracingTest.php:101)
 at PHPUnit.Framework.TestCase.runTest(TestCase.php:1527)
 at PHPUnit.Framework.TestCase.runBare(TestCase.php:1133)
 at PHPUnit.Framework.TestResult.run(TestResult.php:722)
 at PHPUnit.Framework.TestCase.run(TestCase.php:885)
 at PHPUnit.Framework.TestSuite.run(TestSuite.php:677)
 ... 6 more';
        $actualStacktrace = '';
        
        try {
            throw new \Exception('Thrown from here');
        } catch (\Exception $e) {
            $actualStacktrace = Span::getStackTrace($e);
        }

        $this->assertEquals($stacktrace, $actualStacktrace);
    }

    //FUNCTIONS FOR BELOW TEST, MUST TEST CAUSE
    private static function fail2()
    {
        throw new \Exception('Thrown from fail2()');
    }
    
    private static function fail1()
    {
        try {
            self::fail2();
        } catch (\Exception $e1) {
            throw new \Exception('Thrown from fail1()', 0, $e1);
        }
    }

    public function testGetStackTraceWithCause()
    {
        $stacktrace = 'Exception: Thrown from fail1()
 at OpenTelemetry.Tests.Sdk.Unit.Trace.TracingTest.fail1(TracingTest.php:120)
 at OpenTelemetry.Tests.Sdk.Unit.Trace.TracingTest.testGetStackTraceWithCause(TracingTest.php:143)
 at PHPUnit.Framework.TestCase.runTest(TestCase.php:1527)
 at PHPUnit.Framework.TestCase.runBare(TestCase.php:1133)
 at PHPUnit.Framework.TestResult.run(TestResult.php:722)
 at PHPUnit.Framework.TestCase.run(TestCase.php:885)
 at PHPUnit.Framework.TestSuite.run(TestSuite.php:677)
 ... 6 more
Caused by: Exception: Thrown from fail2()
 at OpenTelemetry.Tests.Sdk.Unit.Trace.TracingTest.fail2(TracingTest.php:112)
 at OpenTelemetry.Tests.Sdk.Unit.Trace.TracingTest.fail1(TracingTest.php:118)
 ... 12 more';
 
        $actualStacktrace = '';

        try {
            self::fail1();
        } catch (\Exception $e) {
            $actualStacktrace = Span::getStackTrace($e);
        }

        $this->assertEquals($stacktrace, $actualStacktrace);
    }

    public function testCreateSpan()
    {
        $tracerProvider = new SDK\TracerProvider();
        $tracer = $tracerProvider->getTracer('OpenTelemetry.TracingTest');
        $tracer->startAndActivateSpan('firstSpan');
        $global = $tracer->getActiveSpan();

        /** @var SDK\Span $mysql */
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

        // According to spec, the default span status is UNSET.
        self::assertFalse($mysql->isStatusOK());

        // active span rolled back
        $this->assertSame($tracer->getActiveSpan(), $global);
    }

    public function testCreateSpanWithSampler()
    {
        $tracerProvider = new SDK\TracerProvider(null, new SDK\Sampler\AlwaysOffSampler());
        $tracer = $tracerProvider->getTracer('OpenTelemetry.TracingTest');
        $tracer->startAndActivateSpan('firstSpan');
        $global = $tracer->getActiveSpan();
        $this->assertSame($tracer->getActiveSpan(), $global);

        /** @var SDK\Span $mysql */
        $mysql = $tracer->startAndActivateSpan('mysql');
        $this->assertSame($tracer->getActiveSpan(), $mysql);
        $this->assertSame($global->getContext()->getTraceId(), $mysql->getContext()->getTraceId());
        $this->assertNotNull($mysql->getStartEpochTimestamp());
        $this->assertFalse($mysql->isRecording());
        $this->assertNull($mysql->getDuration());

        $mysql->end();
        $this->assertFalse($mysql->isRecording());
        $this->assertNull($mysql->getDuration());

        // According to spec, the default span status is UNSET.
        self::assertFalse($mysql->isStatusOK());

        // active span rolled back
        $this->assertSame($tracer->getActiveSpan(), $global);
    }

    public function testCreateSpanWithSpanKind()
    {
        $tracerProvider = new SDK\TracerProvider(null, new SDK\Sampler\AlwaysOnSampler());
        $tracer = $tracerProvider->getTracer('OpenTelemetry.TracingTest');

        $span = $tracer->startAndActivateSpan('someSpan');
        $this->assertSame($span->getSpanKind(), API\SpanKind::KIND_INTERNAL);
        $span->end();

        $span = $tracer->startAndActivateSpan('someSpan', API\SpanKind::KIND_CLIENT);
        $this->assertSame($span->getSpanKind(), API\SpanKind::KIND_CLIENT);
        $span->end();

        $span = $tracer->startAndActivateSpan('someSpan', API\SpanKind::KIND_SERVER);
        $this->assertSame($span->getSpanKind(), API\SpanKind::KIND_SERVER);
        $span->end();

        $span = $tracer->startAndActivateSpan('someSpan', API\SpanKind::KIND_PRODUCER);
        $this->assertSame($span->getSpanKind(), API\SpanKind::KIND_PRODUCER);
        $span->end();

        $span = $tracer->startAndActivateSpan('someSpan', API\SpanKind::KIND_CONSUMER);
        $this->assertSame($span->getSpanKind(), API\SpanKind::KIND_CONSUMER);
        $span->end();
    }

    public function testGetStatus()
    {
        $tracerProvider = new SDK\TracerProvider();
        /** @var Tracer $tracer */
        $tracer = $tracerProvider->getTracer('OpenTelemetry.TracingTest');

        $span = $tracer->startAndActivateSpan('setSpanStatus');

        // If an invalid code is given, SpanStatus should be/remain UNSET.
        $firstStatus = $span->setSpanStatus('MY_CODE');
        $status = $firstStatus->getStatus();
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());

        $firstStatus = $span->setSpanStatus(SpanStatus::UNSET);
        $status = $firstStatus->getStatus();
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());

        $firstStatus = $span->setSpanStatus(SpanStatus::OK);
        $status = $firstStatus->getStatus();
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::OK], $status->getStatusDescription());
        self::assertEquals(SpanStatus::OK, $status->getCanonicalStatusCode());

        $firstStatus = $span->setSpanStatus(SpanStatus::ERROR);
        $status = $firstStatus->getStatus();
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::ERROR], $status->getStatusDescription());
        self::assertEquals(SpanStatus::ERROR, $status->getCanonicalStatusCode());

        /*
         * Only ERROR codes should modify span status description.
         * Description MUST only be used with the Error.
         */
        $firstStatus = $span->setSpanStatus('mycode', 'Neunundneunzig Luftballons');
        $status = $firstStatus->getStatus();
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());

        $firstStatus = $span->setSpanStatus(SpanStatus::OK, 'Neunundneunzig Luftballons');
        $status = $firstStatus->getStatus();
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::OK], $status->getStatusDescription());
        self::assertEquals(SpanStatus::OK, $status->getCanonicalStatusCode());

        $firstStatus = $span->setSpanStatus(SpanStatus::UNSET, 'Neunundneunzig Luftballons');
        $status = $firstStatus->getStatus();
        self::assertEquals(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $status->getStatusDescription());
        self::assertEquals(SpanStatus::UNSET, $status->getCanonicalStatusCode());

        $firstStatus = $span->setSpanStatus(SpanStatus::ERROR, 'Neunundneunzig Luftballons');
        $status = $firstStatus->getStatus();
        self::assertEquals('Neunundneunzig Luftballons', $status->getStatusDescription());
        self::assertEquals(SpanStatus::ERROR, $status->getCanonicalStatusCode());
    }

    public function testStatusManipulation()
    {
        $tracerProvider = new SDK\TracerProvider();
        /** @var Tracer $tracer */
        $tracer = $tracerProvider->getTracer('OpenTelemetry.TracingTest');

        $span = $tracer->startAndActivateSpan('setSpanStatus')
            ->setSpanStatus(SpanStatus::ERROR);
        self::assertFalse($span->isStatusOK());
        self::assertSame(SpanStatus::ERROR, $span->getCanonicalStatusCode());
        self::assertSame(SpanStatus::DESCRIPTION[SpanStatus::ERROR], $span->getStatusDescription());

        $span->setSpanStatus(SpanStatus::UNSET);

        self::assertFalse($span->isStatusOK());
        self::assertSame(SpanStatus::UNSET, $span->getCanonicalStatusCode());
        self::assertSame(SpanStatus::DESCRIPTION[SpanStatus::UNSET], $span->getStatusDescription());

        $span->setSpanStatus(SpanStatus::OK);

        self::assertTrue($span->isStatusOK());
        self::assertSame(SpanStatus::OK, $span->getCanonicalStatusCode());
        self::assertSame(SpanStatus::DESCRIPTION[SpanStatus::OK], $span->getStatusDescription());

        self::assertCount(1, $tracer->getSpans());
        $span->end();
    }

    public function testSetSpanStatusWhenNotRecording()
    {
        $tracerProvider = new SDK\TracerProvider();
        $tracer = $tracerProvider->getTracer('OpenTelemetry.TracingTest');

        $span = $tracer->startAndActivateSpan('span')
            ->setSpanStatus(SpanStatus::ERROR, 'my description')
            ->end()
            ->setSpanStatus(SpanStatus::UNSET, 'nope');

        $this->assertEquals(SpanStatus::new(SpanStatus::ERROR, 'my description'), $span->getStatus());
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
        /** @var Attribute $email */
        $email = $span->getAttribute('email');
        self::assertEquals(new Attribute('email', 'nekufa@gmail.com'), $email);

        // set attribute
        $span->setAttribute('username', 'nekufa');
        $attributes = $span->getAttributes();
        self::assertCount(2, $attributes);
        /** @var Attribute $username */
        $username = $span->getAttribute('username');
        self::assertEquals(new Attribute('email', 'nekufa@gmail.com'), $email);
        self::assertEquals(new Attribute('username', 'nekufa'), $username);

        // attribute key - code coverage
        self::assertEquals('email', $email->getKey());
        self::assertEquals('username', $username->getKey());

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
        /** @var SDK\Span $span */
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

    public function testRecordExceptionEventRegistration()
    {
        $tracerProvider = new SDK\TracerProvider();
        $tracer = $tracerProvider->getTracer('OpenTelemetry.TracingTest');
        $span = $tracer->startAndActivateSpan('zerodivisiontest');

        try {
            throw new Exception('Record exception test event');
        } catch (Exception $exception) {
            $span->recordException($exception);
        }

        $events = $span->getEvents();
        self::assertCount(1, $events);

        [$event] = iterator_to_array($events);

        $this->assertSame($event->getName(), 'exception');
        $this->assertArrayHasKey('exception.type', iterator_to_array($event->getAttributes()));
        $this->assertArrayHasKey('exception.message', iterator_to_array($event->getAttributes()));
        $this->assertArrayHasKey('exception.stacktrace', iterator_to_array($event->getAttributes()));

        $timestamp = Clock::get()->timestamp();
        $span->addEvent('update', $timestamp)
                    ->setAttribute('space', 'guard.session')
                    ->setAttribute('id', 67235)
                    ->setAttribute('active_at', time());

        $this->assertCount(2, $span->getEvents());
    }

    public function testRecordExceptionEventAdditionalAttributes()
    {
        $tracerProvider = new SDK\TracerProvider();
        $tracer = $tracerProvider->getTracer('OpenTelemetry.TracingTest');
        $span = $tracer->startSpan('span');

        $span->recordException(new Exception('exception'), new Attributes([
            'exception.message' => 'message',
            'exception.escaped' => true,
        ]));

        [$event] = iterator_to_array($span->getEvents());

        $this->assertArrayHasKey('exception.type', iterator_to_array($event->getAttributes()));
        $this->assertArrayHasKey('exception.message', iterator_to_array($event->getAttributes()));
        $this->assertArrayHasKey('exception.stacktrace', iterator_to_array($event->getAttributes()));
        $this->assertArrayHasKey('exception.escaped', iterator_to_array($event->getAttributes()));

        $this->assertSame('message', iterator_to_array($event->getAttributes())['exception.message']->getValue());
        $this->assertTrue(iterator_to_array($event->getAttributes())['exception.escaped']->getValue());
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
        $requestParent = $request->getParent();
        $this->assertNotNull($requestParent);
        $this->assertSame($requestParent->getSpanId(), $global->getContext()->getSpanId());
        $this->assertNull($global->getParent());
    }

    public function testActiveRootSpanIsNoopSpanIfNoParentProvided()
    {
        $tracer = (new SDK\TracerProvider())->getTracer('OpenTelemetry.TracingTest');

        $this->assertInstanceOf(
            SDK\NoopSpan::class,
            $tracer->getActiveSpan()
        );
    }

    public function testCreateSpanResourceNonDefaultTraceProviderNonDefaultTrace()
    {
        /*
         * A resource can be associated with the TracerProvider when the TracerProvider is created.
         * That association cannot be changed later. When associated with a TracerProvider, all
         * Spans produced by any Tracer from the provider MUST be associated with this Resource.
         */

        // Create a new provider with a resource containing 2 attributes.
        $providerResource = ResourceInfo::create(new Attributes(['provider' => 'primary', 'empty' => '']));
        $traceProvider = new TracerProvider($providerResource);
        $tpAttributes = $traceProvider->getResource()->getAttributes();

        // Verify the resource associated with the trace provider.
        $this->assertCount(5, $tpAttributes);
        /** @var Attribute $primary */
        $primary = $tpAttributes->getAttribute('provider');
        /** @var Attribute $empty */
        $empty = $tpAttributes->getAttribute('empty');
        $this->assertEquals('primary', $primary->getValue());
        $this->assertEquals('', $empty->getValue());

        // Add a Tracer.  The trace provider should add its resource to the new Tracer.
        /** @var Tracer $tracer */
        $tracer = $traceProvider->getTracer('name', 'version');
        $resource = $tracer->getResource();
        $attributes = $resource->getAttributes();

        // Verify the resource associated with the tracer.
        /** @var Attribute $name */
        $name = $resource->getAttributes()->getAttribute('name');
        /** @var Attribute $sdkname */
        $sdkname = $resource->getAttributes()->getAttribute(ResourceConstants::TELEMETRY_SDK_NAME);
        /** @var Attribute $sdklanguage */
        $sdklanguage = $resource->getAttributes()->getAttribute(ResourceConstants::TELEMETRY_SDK_LANGUAGE);
        /** @var Attribute $sdkversion */
        $sdkversion = $resource->getAttributes()->getAttribute(ResourceConstants::TELEMETRY_SDK_VERSION);

        /** @var Attribute $primary */
        $primary = $attributes->getAttribute('provider');
        /** @var Attribute $empty */
        $empty = $attributes->getAttribute('empty');
        $this->assertEquals('primary', $primary->getValue());
        $this->assertEquals('', $empty->getValue());

        $this->assertEquals('opentelemetry', $sdkname->getValue());
        $this->assertEquals('php', $sdklanguage->getValue());
        $this->assertEquals('dev', $sdkversion->getValue());

        $this->assertCount(5, $attributes);

        // Start a span with the tracer.
        $tracer->startAndActivateSpan('firstSpan');

        /** @var Span $global */
        $global = $tracer->getActiveSpan();
        $this->assertSame($tracer->getActiveSpan(), $global);

        // Verify the resource associated with the span.
        /** @var Attributes $attributes */
        $attributes = $global->getResource()->getAttributes();

        /** @var Attribute $name */
        $name = $resource->getAttributes()->getAttribute('name');
        /** @var Attribute $sdkname */
        $sdkname = $resource->getAttributes()->getAttribute(ResourceConstants::TELEMETRY_SDK_NAME);
        /** @var Attribute $sdklanguage */
        $sdklanguage = $resource->getAttributes()->getAttribute(ResourceConstants::TELEMETRY_SDK_LANGUAGE);
        /** @var Attribute $sdkversion */
        $sdkversion = $resource->getAttributes()->getAttribute(ResourceConstants::TELEMETRY_SDK_VERSION);

        /** @var Attribute $primary */
        $primary = $attributes->getAttribute('provider');
        /** @var Attribute $empty */
        $empty = $attributes->getAttribute('empty');
        $this->assertEquals('primary', $primary->getValue());
        $this->assertEquals('', $empty->getValue());

        $this->assertEquals('opentelemetry', $sdkname->getValue());
        $this->assertEquals('php', $sdklanguage->getValue());
        $this->assertEquals('dev', $sdkversion->getValue());

        $this->assertCount(5, $attributes);
    }

    public function testCreateSpanGetsResourceFromDefaultTraceProviderDefaultTrace()
    {
        /*
         * A resource can be associated with the TracerProvider when the TracerProvider is created.
         * That association cannot be changed later. When associated with a TracerProvider, all
         * Spans produced by any Tracer from the provider MUST be associated with this Resource.
         */

        // Create a new provider.
        $traceProvider = new TracerProvider();
        $tpAttributes = $traceProvider->getResource()->getAttributes();

        // Verify the resource associated with the trace provider.
        $this->assertCount(0, $tpAttributes);

        // Add a Tracer.  The trace provider should merge its resource one inherited from the traceprovider.
        /** @var Tracer $tracer */
        $tracer = $traceProvider->getTracer('name');
        $resource = $tracer->getResource();
        $attributes = $resource->getAttributes();

        // Verify the resource associated with the tracer.
        /** @var Attribute $sdkname */
        $sdkname = $resource->getAttributes()->getAttribute(ResourceConstants::TELEMETRY_SDK_NAME);
        /** @var Attribute $sdklanguage */
        $sdklanguage = $resource->getAttributes()->getAttribute(ResourceConstants::TELEMETRY_SDK_LANGUAGE);
        /** @var Attribute $sdkversion */
        $sdkversion = $resource->getAttributes()->getAttribute(ResourceConstants::TELEMETRY_SDK_VERSION);

        $this->assertEquals('opentelemetry', $sdkname->getValue());
        $this->assertEquals('php', $sdklanguage->getValue());
        $this->assertEquals('dev', $sdkversion->getValue());

        $this->assertCount(3, $attributes);

        // Start a span with the tracer.
        $tracer->startAndActivateSpan('firstSpan');
        /** @var Span $global */
        $global = $tracer->getActiveSpan();
        $this->assertSame($tracer->getActiveSpan(), $global);

        // Verify the resource associated with the span.

        $attributes = $global->getResource()->getAttributes();

        /** @var Attribute $sdkname */
        $sdkname = $resource->getAttributes()->getAttribute(ResourceConstants::TELEMETRY_SDK_NAME);
        /** @var Attribute $sdklanguage */
        $sdklanguage = $resource->getAttributes()->getAttribute(ResourceConstants::TELEMETRY_SDK_LANGUAGE);
        /** @var Attribute $sdkversion */
        $sdkversion = $resource->getAttributes()->getAttribute(ResourceConstants::TELEMETRY_SDK_VERSION);

        $this->assertEquals('opentelemetry', $sdkname->getValue());
        $this->assertEquals('php', $sdklanguage->getValue());
        $this->assertEquals('dev', $sdkversion->getValue());

        $this->assertCount(3, $attributes);
    }

    public function testCreateSpanGetsResourceFromNonDefaultTraceProviderDefaultTrace()
    {
        /*
         * A resource can be associated with the TracerProvider when the TracerProvider is created.
         * That association cannot be changed later. When associated with a TracerProvider, all
         * Spans produced by any Tracer from the provider MUST be associated with this Resource.
         */

        // Create a new provider with a resource containing 2 attributes.
        $providerResource = ResourceInfo::create(new Attributes(['provider' => 'primary', 'empty' => '']));
        $traceProvider = new TracerProvider($providerResource);
        $tpAttributes = $traceProvider->getResource()->getAttributes();

        // Verify the resource associated with the trace provider.
        $this->assertCount(5, $tpAttributes);

        /** @var Attribute $primary */
        $primary = $tpAttributes->getAttribute('provider');
        /** @var Attribute $empty */
        $empty = $tpAttributes->getAttribute('empty');
        $this->assertEquals('primary', $primary->getValue());
        $this->assertEquals('', $empty->getValue());

        // Add a Tracer.  The trace provider should add its resource to the new Tracer.
        /** @var Tracer $tracer */
        $tracer = $traceProvider->getTracer('name');
        $resource = $tracer->getResource();
        $attributes = $resource->getAttributes();

        // Verify the resource associated with the tracer.
        /** @var Attribute $name */
        $name = $resource->getAttributes()->getAttribute('name');
        /** @var Attribute $sdkname */
        $sdkname = $resource->getAttributes()->getAttribute(ResourceConstants::TELEMETRY_SDK_NAME);
        /** @var Attribute $sdklanguage */
        $sdklanguage = $resource->getAttributes()->getAttribute(ResourceConstants::TELEMETRY_SDK_LANGUAGE);
        /** @var Attribute $sdkversion */
        $sdkversion = $resource->getAttributes()->getAttribute(ResourceConstants::TELEMETRY_SDK_VERSION);

        /** @var Attribute $primary */
        $primary = $attributes->getAttribute('provider');
        /** @var Attribute $empty */
        $empty = $attributes->getAttribute('empty');
        $this->assertEquals('primary', $primary->getValue());
        $this->assertEquals('', $empty->getValue());

        $this->assertEquals('opentelemetry', $sdkname->getValue());
        $this->assertEquals('php', $sdklanguage->getValue());
        $this->assertEquals('dev', $sdkversion->getValue());

        $this->assertCount(5, $attributes);

        // Start a span with the tracer.
        $tracer->startAndActivateSpan('firstSpan');

        /** @var Span $global */
        $global = $tracer->getActiveSpan();
        $this->assertSame($tracer->getActiveSpan(), $global);

        // Verify the resource associated with the span.

        $attributes = $global->getResource()->getAttributes();

        /** @var Attribute $sdkname */
        $sdkname = $resource->getAttributes()->getAttribute(ResourceConstants::TELEMETRY_SDK_NAME);
        /** @var Attribute $sdklanguage */
        $sdklanguage = $resource->getAttributes()->getAttribute(ResourceConstants::TELEMETRY_SDK_LANGUAGE);
        /** @var Attribute $sdkversion */
        $sdkversion = $resource->getAttributes()->getAttribute(ResourceConstants::TELEMETRY_SDK_VERSION);

        /** @var Attribute $primary */
        $primary = $attributes->getAttribute('provider');
        /** @var Attribute $empty */
        $empty = $attributes->getAttribute('empty');
        $this->assertEquals('primary', $primary->getValue());
        $this->assertEquals('', $empty->getValue());

        $this->assertEquals('opentelemetry', $sdkname->getValue());
        $this->assertEquals('php', $sdklanguage->getValue());
        $this->assertEquals('dev', $sdkversion->getValue());

        $this->assertCount(5, $attributes);
    }

    public function testCreateSpanGetsResourceFromDefaultTraceProviderNonDefaultTrace()
    {
        /*
         * A resource can be associated with the TracerProvider when the TracerProvider is created.
         * That association cannot be changed later. When associated with a TracerProvider, all
         * Spans produced by any Tracer from the provider MUST be associated with this Resource.
         */

        // Create a new provider.
        $traceProvider = new TracerProvider();
        $tpAttributes = $traceProvider->getResource()->getAttributes();

        // Verify the resource associated with the trace provider.
        $this->assertCount(0, $tpAttributes);

        // Add a Tracer.  The trace provider should merge its resource one inherited from the traceprovider.
        /** @var Tracer $tracer */
        $tracer = $traceProvider->getTracer('name', 'version');
        $resource = $tracer->getResource();
        $attributes = $resource->getAttributes();

        // Verify the resource associated with the tracer.
        /** @var Attribute $sdkname */
        $sdkname = $resource->getAttributes()->getAttribute(ResourceConstants::TELEMETRY_SDK_NAME);
        /** @var Attribute $sdklanguage */
        $sdklanguage = $resource->getAttributes()->getAttribute(ResourceConstants::TELEMETRY_SDK_LANGUAGE);
        /** @var Attribute $sdkversion */
        $sdkversion = $resource->getAttributes()->getAttribute(ResourceConstants::TELEMETRY_SDK_VERSION);

        $this->assertEquals('opentelemetry', $sdkname->getValue());
        $this->assertEquals('php', $sdklanguage->getValue());
        $this->assertEquals('dev', $sdkversion->getValue());

        $this->assertCount(3, $attributes);

        // Start a span with the tracer.
        $tracer->startAndActivateSpan('firstSpan');

        /** @var Span $global */
        $global = $tracer->getActiveSpan();
        $this->assertSame($tracer->getActiveSpan(), $global);

        // Verify the resource associated with the span.

        $attributes = $global->getResource()->getAttributes();

        /** @var Attribute $sdkname */
        $sdkname = $resource->getAttributes()->getAttribute(ResourceConstants::TELEMETRY_SDK_NAME);
        /** @var Attribute $sdklanguage */
        $sdklanguage = $resource->getAttributes()->getAttribute(ResourceConstants::TELEMETRY_SDK_LANGUAGE);
        /** @var Attribute $sdkversion */
        $sdkversion = $resource->getAttributes()->getAttribute(ResourceConstants::TELEMETRY_SDK_VERSION);

        $this->assertEquals('opentelemetry', $sdkname->getValue());
        $this->assertEquals('php', $sdklanguage->getValue());
        $this->assertEquals('dev', $sdkversion->getValue());

        $this->assertCount(3, $attributes);
    }
}
