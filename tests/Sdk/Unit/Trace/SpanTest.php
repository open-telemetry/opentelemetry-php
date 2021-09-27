<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Trace;

use Exception;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Sdk\InstrumentationLibrary;
use OpenTelemetry\Sdk\Resource\ResourceInfo;
use OpenTelemetry\Sdk\Trace\Attributes;
use OpenTelemetry\Sdk\Trace\Clock;
use OpenTelemetry\Sdk\Trace\Events;
use OpenTelemetry\Sdk\Trace\IdGenerator;
use OpenTelemetry\Sdk\Trace\Link;
use OpenTelemetry\Sdk\Trace\Links;
use OpenTelemetry\Sdk\Trace\RandomIdGenerator;
use OpenTelemetry\Sdk\Trace\Span;
use OpenTelemetry\Sdk\Trace\SpanContext;
use OpenTelemetry\Sdk\Trace\SpanData;
use OpenTelemetry\Sdk\Trace\SpanProcessor;
use OpenTelemetry\Sdk\Trace\StatusData;
use OpenTelemetry\Sdk\Trace\Test\TestClock;
use OpenTelemetry\Trace as API;

class SpanTest extends MockeryTestCase
{
    private const SPAN_NAME = 'test_span';
    private const NEW_SPAN_NAME = 'new_test_span';
    private const START_EPOCH = 1000123789654;

    /** @var MockInterface&SpanProcessor */
    private $spanProcessor;

    private IdGenerator $idGenerator;
    private ResourceInfo $resource;
    private InstrumentationLibrary $instrumentationLibrary;
    private API\SpanContext $spanContext;
    private TestClock $testClock;

    private API\Attributes $attributes;
    private API\Attributes $expectedAttributes;
    private API\Link $link;

    private string $traceId;
    private string $spanId;
    private string $parentSpanId;

    protected function setUp():void
    {
        $this->idGenerator = new RandomIdGenerator();
        $this->resource = ResourceInfo::emptyResource();
        $this->instrumentationLibrary = new InstrumentationLibrary('test_library', '0.1.2');

        $this->spanProcessor = Mockery::spy(SpanProcessor::class);

        $this->traceId = $this->idGenerator->generateTraceId();
        $this->spanId = $this->idGenerator->generateSpanId();
        $this->parentSpanId = $this->idGenerator->generateSpanId();

        $this->spanContext = SpanContext::create($this->traceId, $this->spanId);
        $this->testClock = new TestClock(self::START_EPOCH);

        $this->link = new Link($this->spanContext);
        $this->attributes = new Attributes([
            'some_string_key' => 'some_string_value',
            'float_attribute' => 3.14,
            'bool_attribute' => false,
        ]);

        $this->expectedAttributes = clone $this->attributes;

        Clock::setTestClock($this->testClock);
    }

    protected function tearDown(): void
    {
        Clock::setTestClock();
    }

    // region API

    public function test_getCurrentSpan_default(): void
    {
        $this->assertSame(
            Span::getInvalid(),
            Span::getCurrent()
        );
    }

    public function test_getCurrentSpan_setSpan(): void
    {
        $span = Span::wrap(SpanContext::getInvalid());

        $scope = $span->activate();

        $this->assertSame(
            $span,
            Span::getCurrent()
        );

        $scope->close();
    }

    public function test_getSpan_defaultContext(): void
    {
        $span = Span::fromContext(Context::getRoot());

        $this->assertSame(
            $span,
            Span::getInvalid()
        );
    }

    public function test_getSpan_explicitContext(): void
    {
        $span = Span::fromContext(Context::getRoot());

        $this->assertSame(
            $span,
            Span::fromContext(
                Context::getRoot()->withContextValue($span)
            )
        );
    }

    public function test_inProcessContext(): void
    {
        $span = Span::wrap(SpanContext::getInvalid());
        $scope = $span->activate();
        $this->assertSame($span, Span::getCurrent());

        $secondSpan = Span::wrap(SpanContext::getInvalid());

        $scope2 = $secondSpan->activate();

        $this->assertSame($secondSpan, Span::getCurrent());

        $scope2->close();
        $secondSpan->end();

        $this->assertSame($span, Span::getCurrent());

        $scope->close();
        $span->end();
    }

    // endregion API

    // region SDK

    public function test_nothingChangesAfterEnd(): void
    {
        $span = $this->createTestSpan();
        $span->end();

        // Ensure adding/updating fields after end noop.
        $this->spanDoWork($span, API\StatusCode::STATUS_ERROR, 'ERR');

        $this->assertSpanData(
            $span->toSpanData(),
            new Attributes(),
            new Events(),
            (new Links())->addLink($this->link),
            self::SPAN_NAME,
            self::START_EPOCH,
            self::START_EPOCH,
            API\StatusCode::STATUS_UNSET,
            true
        );
    }

    public function test_end_twice(): void
    {
        $span = $this->createTestSpan();
        $this->assertFalse($span->hasEnded());
        $span->end();
        $this->assertTrue($span->hasEnded());
        $span->end();
        $this->assertTrue($span->hasEnded());
    }

    public function test_toSpanData_activeSpan(): void
    {
        $span = $this->createTestSpan();

        $this->assertFalse($span->hasEnded());
        $this->spanDoWork($span);

        $this->assertSpanData(
            $span->toSpanData(),
            $this->expectedAttributes,
            (new Events())->addEvent('event2', null, self::START_EPOCH + API\Clock::NANOS_PER_SECOND),
            (new Links())->addLink($this->link),
            self::NEW_SPAN_NAME,
            self::START_EPOCH,
            0,
            API\StatusCode::STATUS_UNSET,
            false
        );

        $this->assertFalse($span->hasEnded());
        $this->assertTrue($span->isRecording());

        $span->end();

        $this->assertTrue($span->hasEnded());
        $this->assertFalse($span->isRecording());
    }

    public function test_toSpanData_endedSpan(): void
    {
        $span = $this->createTestSpan();
        $this->spanDoWork($span, API\StatusCode::STATUS_ERROR, 'ERR');
        $span->end();

        $this
            ->spanProcessor
            ->shouldHaveReceived('onEnd')
            ->once()
            ->with($span);

        $this->assertSpanData(
            $span->toSpanData(),
            $this->expectedAttributes,
            (new Events())->addEvent('event2', null, self::START_EPOCH + API\Clock::NANOS_PER_SECOND),
            (new Links())->addLink($this->link),
            self::NEW_SPAN_NAME,
            self::START_EPOCH,
            $this->testClock->now(),
            API\StatusCode::STATUS_ERROR,
            true
        );
    }

    public function test_toSpanData_rootSpan(): void
    {
        $span = $this->createTestRootSpan();
        $this->spanDoWork($span);
        $span->end();

        $this->assertFalse($span->getParentContext()->isValid());
        $this->assertFalse(SpanContext::isValidSpanId($span->toSpanData()->getParentSpanId()));
    }

    public function test_toSpanData_childSpan(): void
    {
        $span = $this->createTestSpan();
        $this->spanDoWork($span);
        $span->end();

        $this->assertTrue($span->getParentContext()->isValid());
        $this->assertSame($this->traceId, $span->getParentContext()->getTraceId());
        $this->assertSame($this->parentSpanId, $span->getParentContext()->getSpanId());
        $this->assertSame($this->parentSpanId, $span->toSpanData()->getParentSpanId());
    }

    public function test_toSpanData_initialAttributes(): void
    {
        $span = $this->createTestSpanWithAttributes($this->attributes);
        $span->setAttribute('another_key', 'another_value');
        $span->end();

        $spanData = $span->toSpanData();
        $this->assertSame($this->attributes->count() + 1, $spanData->getAttributes()->count());
        $this->assertSame(0, $spanData->getTotalDroppedAttributes());
    }

    public function test_toSpanData_isImmutable(): void
    {
        $span = $this->createTestSpanWithAttributes($this->attributes);

        $spanData = $span->toSpanData();

        $span->setAttribute('another_key', 'another_value');
        $span->updateName('a_new_name');
        $span->addEvent('new_event');
        $span->end();

        $this->assertSame($this->attributes->count(), $spanData->getAttributes()->count());
        $this->assertNull($spanData->getAttributes()->get('another_key'));
        $this->assertFalse($spanData->hasEnded());
        $this->assertSame(0, $spanData->getEndEpochNanos());
        $this->assertSame($spanData->getName(), self::SPAN_NAME);
        $this->assertEmpty($spanData->getEvents());

        $spanData = $span->toSpanData();

        $this->assertSame($this->attributes->count() + 1, $spanData->getAttributes()->count());
        $this->assertSame('another_value', $spanData->getAttributes()->get('another_key'));
        $this->assertTrue($spanData->hasEnded());
        $this->assertGreaterThan(0, $spanData->getEndEpochNanos());
        $this->assertSame($spanData->getName(), 'a_new_name');
        $this->assertCount(1, $spanData->getEvents());
    }

    public function test_toSpanData_status(): void
    {
        $span = $this->createTestSpan(API\SpanKind::KIND_CONSUMER);
        $this->testClock->advanceSeconds();
        $this->assertSame(StatusData::unset(), $span->toSpanData()->getStatus());
        $span->setStatus(API\StatusCode::STATUS_ERROR, 'ERR');
        $this->assertEquals(StatusData::create(API\StatusCode::STATUS_ERROR, 'ERR'), $span->toSpanData()->getStatus());
        $span->end();
        $this->assertEquals(StatusData::create(API\StatusCode::STATUS_ERROR, 'ERR'), $span->toSpanData()->getStatus());
    }

    public function test_toSpanData_kind(): void
    {
        $span = $this->createTestSpan(API\SpanKind::KIND_SERVER);
        $this->assertSame(API\SpanKind::KIND_SERVER, $span->toSpanData()->getKind());
        $span->end();
    }

    public function test_getKind(): void
    {
        $span = $this->createTestSpan(API\SpanKind::KIND_SERVER);
        $this->assertSame(API\SpanKind::KIND_SERVER, $span->getKind());
        $span->end();
    }

    public function test_getAttribute(): void
    {
        $span = $this->createTestSpanWithAttributes($this->attributes);
        $this->assertSame(3.14, $span->getAttribute('float_attribute'));
        $span->end();
    }

    public function test_setAttribute_emptyKey(): void
    {
        $span = $this->createTestSpan();
        $this->assertEmpty($span->toSpanData()->getAttributes());
        $span->setAttribute('  ', 123);
        $this->assertEmpty($span->toSpanData()->getAttributes());
    }

    public function test_getInstrumentationLibraryInfo(): void
    {
        $span = $this->createTestSpanWithAttributes($this->attributes);
        $this->assertSame($this->instrumentationLibrary, $span->getInstrumentationLibrary());
        $span->end();
    }

    public function test_updateSpanName(): void
    {
        $span = $this->createTestRootSpan();
        $this->assertSame(self::SPAN_NAME, $span->getName());
        $span->updateName(self::NEW_SPAN_NAME);
        $this->assertSame(self::NEW_SPAN_NAME, $span->getName());
        $span->end();
    }

    public function test_getDuration_activeSpan(): void
    {
        $span = $this->createTestSpan();
        $this->testClock->advanceSeconds();
        $elapsedNanos1 = $this->testClock->now() - self::START_EPOCH;
        $this->assertSame($elapsedNanos1, $span->getDuration());
        $this->testClock->advanceSeconds();
        $elapsedNanos2 = $this->testClock->now() - self::START_EPOCH;
        $this->assertSame($elapsedNanos2, $span->getDuration());
        $span->end();
    }

    public function test_getDuration_endedSpan(): void
    {
        $span = $this->createTestSpan();
        $this->testClock->advanceSeconds();
        $span->end();

        $elapsedNanos = $this->testClock->now() - self::START_EPOCH;
        $this->assertSame($elapsedNanos, $span->getDuration());
        $this->testClock->advanceSeconds();
        $this->assertSame($elapsedNanos, $span->getDuration());
    }

    public function test_setAttributes(): void
    {
        $span = $this->createTestSpanWithAttributes($this->attributes);
        $span->setAttributes(new Attributes(['foo' => 'bar']));
        $this->assertCount($this->expectedAttributes->count() + 1, $span->toSpanData()->getAttributes());
    }

    public function test_setAttributes_overridesAttribute(): void
    {
        $span = $this->createTestSpanWithAttributes($this->attributes);
        $this->assertFalse($span->toSpanData()->getAttributes()->get('bool_attribute'));
        $span->setAttributes(new Attributes(['bool_attribute' => true]));
        $this->assertTrue($span->toSpanData()->getAttributes()->get('bool_attribute'));
    }

    public function test_setAttributes_empty(): void
    {
        $span = $this->createTestSpanWithAttributes($this->attributes);
        $span->setAttributes(new Attributes());
        $this->assertEquals($this->expectedAttributes, $span->toSpanData()->getAttributes());
    }

    public function test_recordException(): void
    {
        $exception = new Exception('ERR');
        $span = $this->createTestRootSpan();

        $this->testClock->advance(1000);
        $timestamp = $this->testClock->now();

        $span->recordException($exception);

        $this->assertCount(1, $events = $span->toSpanData()->getEvents());
        $event = $events->getIterator()->current();
        $this->assertSame('exception', $event->getName());
        $this->assertSame($timestamp, $event->getTimestamp());
        $this->assertEquals(
            new Attributes([
                'exception.type' => 'Exception',
                'exception.message' => 'ERR',
                'exception.stacktrace' => Span::formatStackTrace($exception),
            ]),
            $event->getAttributes()
        );
    }

    // endregion SDK

    private function createTestRootSpan(): Span
    {
        return $this
            ->createTestSpan(
                API\SpanKind::KIND_INTERNAL,
                SpanContext::INVALID_SPAN
            );
    }

    /**
     * @psalm-param API\SpanKind::KIND_* $kind
     *
     * @todo: Allow passing in span limits
     */
    private function createTestSpan(
        int $kind = API\SpanKind::KIND_INTERNAL,
        string $parentSpanId = null,
        ?API\Attributes $attributes = null,
        API\Links $links = null
    ): Span {
        $parentSpanId = $parentSpanId ?? $this->parentSpanId;
        $links = $links ?? (new Links())->addLink($this->link);

        $span = Span::startSpan(
            self::SPAN_NAME,
            $this->spanContext,
            $this->instrumentationLibrary,
            $kind,
            $parentSpanId ? Span::wrap(SpanContext::create($this->traceId, $parentSpanId)) : Span::getInvalid(),
            Context::getRoot(),
            $this->spanProcessor,
            $this->resource,
            $attributes,
            $links,
            1,
            0
        );

        $this
            ->spanProcessor
            ->shouldHaveReceived('onStart')
            ->once()
            ->with($span, Context::getRoot());

        return $span;
    }

    public function createTestSpanWithAttributes(API\Attributes $attributes): Span
    {
        return $this
            ->createTestSpan(
                API\SpanKind::KIND_INTERNAL,
                null,
                clone $attributes
            );
    }

    /** @psalm-param API\StatusCode::STATUS_* $status */
    private function spanDoWork(Span $span, ?string $status = null, ?string $description = null): void
    {
        $span->setAttribute('some_string_key', 'some_string_value');

        foreach ($this->attributes as $attribute) {
            // @phpstan-ignore-next-line
            $span->setAttribute($attribute->getKey(), $attribute->getValue());
        }

        $this->testClock->advanceSeconds();
        $span->addEvent('event2');
        $this->testClock->advanceSeconds();
        $span->updateName(self::NEW_SPAN_NAME);
        if ($status) {
            $span->setStatus($status, $description);
        }
    }

    /** @psalm-param API\StatusCode::STATUS_* $status */
    private function assertSpanData(
        SpanData $spanData,
        API\Attributes $attributes,
        API\Events $events,
        API\Links $links,
        string $spanName,
        int $startEpochNanos,
        int $endEpochNanos,
        string $status,
        bool $hasEnded
    ): void {
        $this->assertSame($spanName, $spanData->getName());
        $this->assertSame($this->traceId, $spanData->getTraceId());
        $this->assertSame($this->spanId, $spanData->getSpanId());
        $this->assertSame($this->parentSpanId, $spanData->getParentSpanId());
        $this->assertNull($spanData->getContext()->getTraceState());
        $this->assertSame($this->resource, $spanData->getResource());
        $this->assertSame($this->instrumentationLibrary, $spanData->getInstrumentationLibrary());
        $this->assertEquals($events, $spanData->getEvents());
        $this->assertEquals($links, $spanData->getLinks());
        $this->assertSame($startEpochNanos, $spanData->getStartEpochNanos());
        $this->assertSame($endEpochNanos, $spanData->getEndEpochNanos());
        $this->assertSame($status, $spanData->getStatus()->getCode());
        $this->assertSame($hasEnded, $spanData->hasEnded());
        $this->assertEquals($attributes, $spanData->getAttributes());
    }
}
