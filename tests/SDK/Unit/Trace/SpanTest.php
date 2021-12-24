<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Trace;

use function array_merge;
use Exception;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use OpenTelemetry\API\Trace as API;
use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\AbstractClock;
use OpenTelemetry\SDK\Attributes;
use OpenTelemetry\SDK\InstrumentationLibrary;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Trace\Event;
use OpenTelemetry\SDK\Trace\IdGeneratorInterface;
use OpenTelemetry\SDK\Trace\Link;
use OpenTelemetry\SDK\Trace\RandomIdGenerator;
use OpenTelemetry\SDK\Trace\Span;
use OpenTelemetry\SDK\Trace\SpanDataInterface;
use OpenTelemetry\SDK\Trace\SpanLimits;
use OpenTelemetry\SDK\Trace\SpanLimitsBuilder;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;
use OpenTelemetry\SDK\Trace\StatusData;
use OpenTelemetry\Tests\SDK\Util\TestClock;
use function range;
use function str_repeat;

class SpanTest extends MockeryTestCase
{
    private const SPAN_NAME = 'test_span';
    private const NEW_SPAN_NAME = 'new_test_span';
    private const START_EPOCH = 1000123789654;
    private const ATTRIBUTES = [
        'string_attribute' => 'some_string_value',
        'float_attribute' => 3.14,
        'bool_attribute' => false,
    ];

    /** @var MockInterface&SpanProcessorInterface */
    private $spanProcessor;

    private IdGeneratorInterface $idGenerator;
    private ResourceInfo $resource;
    private InstrumentationLibrary $instrumentationLibrary;
    private API\SpanContextInterface $spanContext;
    private TestClock $testClock;

    private \OpenTelemetry\API\AttributesInterface $expectedAttributes;
    private API\LinkInterface $link;

    private string $traceId;
    private string $spanId;
    private string $parentSpanId;

    protected function setUp():void
    {
        $this->idGenerator = new RandomIdGenerator();
        $this->resource = ResourceInfo::emptyResource();
        $this->instrumentationLibrary = new InstrumentationLibrary('test_library', '0.1.2');

        $this->spanProcessor = Mockery::spy(SpanProcessorInterface::class);

        $this->traceId = $this->idGenerator->generateTraceId();
        $this->spanId = $this->idGenerator->generateSpanId();
        $this->parentSpanId = $this->idGenerator->generateSpanId();

        $this->spanContext = SpanContext::create($this->traceId, $this->spanId);
        $this->testClock = new TestClock(self::START_EPOCH);

        $this->link = new Link($this->spanContext);

        $this->expectedAttributes = new Attributes(
            array_merge(
                ['single_string_attribute' => 'some_string_value'],
                self::ATTRIBUTES
            )
        );

        AbstractClock::setTestClock($this->testClock);
    }

    protected function tearDown(): void
    {
        AbstractClock::setTestClock();
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

        $scope->detach();
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

        $scope2->detach();
        $secondSpan->end();

        $this->assertSame($span, Span::getCurrent());

        $scope->detach();
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
            [],
            [$this->link],
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
            [new Event('event2', self::START_EPOCH + \OpenTelemetry\API\ClockInterface::NANOS_PER_SECOND)],
            [$this->link],
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
            [new Event('event2', self::START_EPOCH + \OpenTelemetry\API\ClockInterface::NANOS_PER_SECOND)],
            [$this->link],
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
        $span = $this->createTestSpanWithAttributes(self::ATTRIBUTES);
        $span->setAttribute('another_key', 'another_value');
        $span->end();

        $spanData = $span->toSpanData();
        $this->assertSame(count(self::ATTRIBUTES) + 1, $spanData->getAttributes()->count());
        $this->assertSame(0, $spanData->getTotalDroppedAttributes());
    }

    public function test_toSpanData_isImmutable(): void
    {
        $span = $this->createTestSpanWithAttributes(self::ATTRIBUTES);

        $spanData = $span->toSpanData();

        $span->setAttribute('another_key', 'another_value');
        $span->updateName('a_new_name');
        $span->addEvent('new_event');
        $span->end();

        $this->assertSame(count(self::ATTRIBUTES), $spanData->getAttributes()->count());
        $this->assertNull($spanData->getAttributes()->get('another_key'));
        $this->assertFalse($spanData->hasEnded());
        $this->assertSame(0, $spanData->getEndEpochNanos());
        $this->assertSame($spanData->getName(), self::SPAN_NAME);
        $this->assertEmpty($spanData->getEvents());

        $spanData = $span->toSpanData();

        $this->assertSame(count(self::ATTRIBUTES) + 1, $spanData->getAttributes()->count());
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
        $span = $this->createTestSpanWithAttributes(self::ATTRIBUTES);
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
        $span = $this->createTestSpanWithAttributes(self::ATTRIBUTES);
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
        $span = $this->createTestRootSpan();

        $attributes = new Attributes([
            'string' => 'str',
            'empty_str' => '',
            'null' => null,
            'str_array' => ['a', 'b'],
        ]);

        $span->setAttributes($attributes);
        $span->end();

        $attributes = $span->toSpanData()->getAttributes();
        $this->assertSame('str', $attributes->get('string'));
        $this->assertSame('', $attributes->get('empty_str'));
        $this->assertNull($attributes->get('null'));
        $this->assertSame(['a', 'b'], $attributes->get('str_array'));
    }

    public function test_setAttributes_overridesAttribute(): void
    {
        $span = $this->createTestSpanWithAttributes(self::ATTRIBUTES);
        $this->assertFalse($span->toSpanData()->getAttributes()->get('bool_attribute'));
        $span->setAttributes(new Attributes(['bool_attribute' => true]));
        $this->assertTrue($span->toSpanData()->getAttributes()->get('bool_attribute'));
    }

    public function test_setAttributes_empty(): void
    {
        $span = $this->createTestRootSpan();
        $span->setAttributes(new Attributes());
        $this->assertEmpty($span->toSpanData()->getAttributes());
    }

    public function test_addEvent(): void
    {
        $span = $this->createTestRootSpan();
        $span->addEvent('event1');
        $span->addEvent('event2', new Attributes(['key1' => 1]));
        $span->addEvent('event3', null, AbstractClock::secondsToNanos(10));

        $span->end();

        $events = $span->toSpanData()->getEvents();
        $this->assertCount(3, $events);
        $idx = 0;

        $this->assertEvent($events[$idx++], 'event1', new Attributes(), self::START_EPOCH);
        $this->assertEvent($events[$idx++], 'event2', new Attributes(['key1' => 1]), self::START_EPOCH);
        $this->assertEvent($events[$idx], 'event3', new Attributes(), AbstractClock::secondsToNanos(10));
    }

    public function test_addEvent_attributeLength(): void
    {
        $maxLength = 25;

        $strVal = str_repeat('a', $maxLength);
        $tooLongStrVal = "${strVal}${strVal}";

        $span = $this->createTestSpan(API\SpanKind::KIND_INTERNAL, (new SpanLimitsBuilder())->setAttributeValueLengthLimit($maxLength)->build());

        $span->addEvent(
            'event',
            new Attributes([
                'string' => $tooLongStrVal,
                'bool' => true,
                'string_array' => [$strVal, $tooLongStrVal],
                'int_array' => [1, 2],
            ])
        );

        $this->assertCount(1, $span->toSpanData()->getEvents());

        $attrs = $span->toSpanData()->getEvents()[0]->getAttributes();
        $this->assertSame($strVal, $attrs->get('string'));
        $this->assertTrue($attrs->get('bool'));
        $this->assertSame(
            [$strVal, $strVal],
            $attrs->get('string_array')
        );
        $this->assertSame(
            [1, 2],
            $attrs->get('int_array')
        );

        $span->end();
    }

    public function test_recordException(): void
    {
        $exception = new Exception('ERR');
        $span = $this->createTestRootSpan();

        $this->testClock->advance(1000);
        $timestamp = $this->testClock->now();

        $span->recordException($exception);

        $this->assertCount(1, $events = $span->toSpanData()->getEvents());
        $event = $events[0];
        $this->assertSame('exception', $event->getName());
        $this->assertSame($timestamp, $event->getEpochNanos());
        $this->assertEquals(
            new Attributes([
                'exception.type' => 'Exception',
                'exception.message' => 'ERR',
                'exception.stacktrace' => Span::formatStackTrace($exception),
            ]),
            $event->getAttributes()
        );
    }

    public function test_recordException_additionalAttributes(): void
    {
        $exception = new Exception('ERR');
        $span = $this->createTestRootSpan();

        $this->testClock->advance(1000);
        $timestamp = $this->testClock->now();

        $span->recordException($exception, new Attributes([
            'foo' => 'bar',
        ]));

        $this->assertCount(1, $events = $span->toSpanData()->getEvents());
        $event = $events[0];
        $this->assertSame('exception', $event->getName());
        $this->assertSame($timestamp, $event->getEpochNanos());
        $this->assertEquals(
            new Attributes([
                'exception.type' => 'Exception',
                'exception.message' => 'ERR',
                'exception.stacktrace' => Span::formatStackTrace($exception),
                'foo' => 'bar',
            ]),
            $event->getAttributes()
        );
    }

    public function test_attributeLength(): void
    {
        $maxLength = 25;

        $strVal = str_repeat('a', $maxLength);
        $tooLongStrVal = "${strVal}${strVal}";

        $span = $this->createTestSpan(
            API\SpanKind::KIND_INTERNAL,
            (new SpanLimitsBuilder())->setAttributeValueLengthLimit($maxLength)->build(),
            null,
            new Attributes([
                'string' => $tooLongStrVal,
                'bool' => true,
                'string_array' => [$strVal, $tooLongStrVal],
                'int_array' => [1, 2],
            ])
        );

        $attrs = $span->toSpanData()->getAttributes();
        $this->assertSame($strVal, $attrs->get('string'));
        $this->assertTrue($attrs->get('bool'));
        $this->assertSame(
            [$strVal, $strVal],
            $attrs->get('string_array')
        );
        $this->assertSame(
            [1, 2],
            $attrs->get('int_array')
        );

        $span->end();
    }

    public function test_droppingAttributes(): void
    {
        $maxNumberOfAttributes = 8;
        $span = $this->createTestSpan(API\SpanKind::KIND_INTERNAL, (new SpanLimitsBuilder())->setAttributeCountLimit($maxNumberOfAttributes)->build());

        foreach (range(1, $maxNumberOfAttributes * 2) as $idx) {
            $span->setAttribute("str_attribute_${idx}", $idx);
        }

        $spanData = $span->toSpanData();

        $this->assertCount($maxNumberOfAttributes, $spanData->getAttributes());
        $this->assertSame(8, $spanData->getTotalDroppedAttributes());

        $span->end();
        $spanData = $span->toSpanData();

        $this->assertCount($maxNumberOfAttributes, $spanData->getAttributes());
        $this->assertSame(8, $spanData->getTotalDroppedAttributes());
    }

    public function test_droppingAttributes_providedViaSpanBuilder(): void
    {
        $maxNumberOfAttributes = 8;

        $attributes = new Attributes();

        foreach (range(1, $maxNumberOfAttributes * 2) as $idx) {
            $attributes->setAttribute("str_attribute_${idx}", $idx);
        }

        $span = $this->createTestSpan(
            API\SpanKind::KIND_INTERNAL,
            (new SpanLimitsBuilder())->setAttributeCountLimit($maxNumberOfAttributes)->build(),
            null,
            $attributes
        );

        $spanData = $span->toSpanData();

        $this->assertCount($maxNumberOfAttributes, $spanData->getAttributes());
        $this->assertSame(8, $spanData->getTotalDroppedAttributes());

        $span->end();
        $spanData = $span->toSpanData();

        $this->assertCount($maxNumberOfAttributes, $spanData->getAttributes());
        $this->assertSame(8, $spanData->getTotalDroppedAttributes());
    }

    public function test_droppingEvents(): void
    {
        $maxNumberOfEvents = 8;
        $span = $this->createTestSpan(API\SpanKind::KIND_INTERNAL, (new SpanLimitsBuilder())->setEventCountLimit($maxNumberOfEvents)->build());

        foreach (range(1, $maxNumberOfEvents * 2) as $_idx) {
            $span->addEvent('event2');
            $this->testClock->advanceSeconds();
        }

        $spanData = $span->toSpanData();
        $this->assertCount($maxNumberOfEvents, $spanData->getEvents());
        $this->assertSame(8, $spanData->getTotalDroppedEvents());

        $span->end();
    }

    // endregion SDK

    private function createTestRootSpan(): Span
    {
        return $this
            ->createTestSpan(
                API\SpanKind::KIND_INTERNAL,
                null,
                SpanContext::INVALID_SPAN
            );
    }

    /**
     * @psalm-param API\SpanKind::KIND_* $kind
     * @param list<API\LinkInterface> $links
     */
    private function createTestSpan(
        int $kind = API\SpanKind::KIND_INTERNAL,
        SpanLimits $spanLimits = null,
        string $parentSpanId = null,
        ?\OpenTelemetry\API\AttributesInterface $attributes = null,
        array $links = []
    ): Span {
        $parentSpanId = $parentSpanId ?? $this->parentSpanId;
        $spanLimits = $spanLimits ?? (new SpanLimitsBuilder())->build();
        $links = $links ?: [$this->link];

        $span = Span::startSpan(
            self::SPAN_NAME,
            $this->spanContext,
            $this->instrumentationLibrary,
            $kind,
            $parentSpanId ? Span::wrap(SpanContext::create($this->traceId, $parentSpanId)) : Span::getInvalid(),
            Context::getRoot(),
            $spanLimits,
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

    public function createTestSpanWithAttributes(array $attributes): Span
    {
        return $this
            ->createTestSpan(
                API\SpanKind::KIND_INTERNAL,
                null,
                null,
                new Attributes($attributes),
            );
    }

    /** @psalm-param API\StatusCode::STATUS_* $status */
    private function spanDoWork(Span $span, ?string $status = null, ?string $description = null): void
    {
        $span->setAttribute('single_string_attribute', 'some_string_value');

        foreach (self::ATTRIBUTES as $key => $value) {
            $span->setAttribute($key, $value);
        }

        $this->testClock->advanceSeconds();
        $span->addEvent('event2');
        $this->testClock->advanceSeconds();
        $span->updateName(self::NEW_SPAN_NAME);
        if ($status) {
            $span->setStatus($status, $description);
        }
    }

    private function assertEvent(
        API\EventInterface $event,
        string $expectedName,
        \OpenTelemetry\API\AttributesInterface $expectedAttributes,
        int $expectedEpochNanos
    ): void {
        $this->assertSame($expectedName, $event->getName());
        $this->assertEquals($expectedAttributes, $event->getAttributes());
        $this->assertSame($expectedEpochNanos, $event->getEpochNanos());
    }

    /**
     * @param list<API\EventInterface> $events
     * @param list<API\LinkInterface> $links
     * @psalm-param API\StatusCode::STATUS_* $status
     */
    private function assertSpanData(
        SpanDataInterface $spanData,
        \OpenTelemetry\API\AttributesInterface $attributes,
        array $events,
        array $links,
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
