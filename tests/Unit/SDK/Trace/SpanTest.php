<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace;

use function array_merge;
use Exception;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use OpenTelemetry\API\Behavior\Internal\Logging;
use OpenTelemetry\API\Behavior\Internal\LogWriter\LogWriterInterface;
use OpenTelemetry\API\Common\Time\Clock;
use OpenTelemetry\API\Common\Time\ClockInterface;
use OpenTelemetry\API\Common\Time\TestClock;
use OpenTelemetry\API\Common\Time\Util as TimeUtil;
use OpenTelemetry\API\Trace as API;
use OpenTelemetry\API\Trace\NonRecordingSpan;
use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\SpanContextValidator;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Common\Exception\StackTraceFormatter;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScope;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\SDK\Trace\Event;
use OpenTelemetry\SDK\Trace\EventInterface;
use OpenTelemetry\SDK\Trace\IdGeneratorInterface;
use OpenTelemetry\SDK\Trace\Link;
use OpenTelemetry\SDK\Trace\LinkInterface;
use OpenTelemetry\SDK\Trace\RandomIdGenerator;
use OpenTelemetry\SDK\Trace\Span;
use OpenTelemetry\SDK\Trace\SpanDataInterface;
use OpenTelemetry\SDK\Trace\SpanLimits;
use OpenTelemetry\SDK\Trace\SpanLimitsBuilder;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;
use OpenTelemetry\SDK\Trace\StatusData;
use PHPUnit\Framework\MockObject\MockObject;
use function range;
use function str_repeat;

/**
 * @covers \OpenTelemetry\SDK\Trace\Span
 */
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
    /** @var LogWriterInterface&MockObject $logWriter */
    private LogWriterInterface $logWriter;

    private IdGeneratorInterface $idGenerator;
    private ResourceInfo $resource;
    private InstrumentationScope $instrumentationScope;
    private API\SpanContextInterface $spanContext;
    private TestClock $testClock;

    private AttributesInterface $expectedAttributes;
    private LinkInterface $link;

    private string $traceId;
    private string $spanId;
    private string $parentSpanId;

    protected function setUp():void
    {
        $this->idGenerator = new RandomIdGenerator();
        $this->resource = ResourceInfoFactory::emptyResource();
        $this->instrumentationScope = new InstrumentationScope('test_scope', '0.1.2', null, Attributes::create([]));

        $this->spanProcessor = Mockery::spy(SpanProcessorInterface::class);

        $this->traceId = $this->idGenerator->generateTraceId();
        $this->spanId = $this->idGenerator->generateSpanId();
        $this->parentSpanId = $this->idGenerator->generateSpanId();

        $this->spanContext = SpanContext::create($this->traceId, $this->spanId);
        $this->testClock = new TestClock(self::START_EPOCH);

        $this->link = new Link($this->spanContext, Attributes::create([]));

        $this->expectedAttributes = Attributes::create(
            array_merge(
                ['single_string_attribute' => 'some_string_value'],
                self::ATTRIBUTES
            )
        );

        Clock::setDefault($this->testClock);
        $this->logWriter = $this->createMock(LogWriterInterface::class);
        Logging::setLogWriter($this->logWriter);
    }

    protected function tearDown(): void
    {
        Clock::reset();
        Logging::reset();
        //        LoggerHolder::unset();
    }

    // region API

    public function test_get_invalid_span(): void
    {
        $this->assertInstanceOf(NonRecordingSpan::class, Span::getInvalid());
    }

    public function test_get_current_span_default(): void
    {
        $this->assertSame(
            Span::getInvalid(),
            Span::getCurrent()
        );
    }

    /**
     * @group trace-compliance
     */
    public function test_start_span(): void
    {
        $this->createTestSpan(API\SpanKind::KIND_INTERNAL);
        $this->spanProcessor
            ->shouldHaveReceived('onStart')
            ->once();
    }

    /**
     * @group trace-compliance
     */
    public function test_end_span(): void
    {
        $span = $this->createTestSpan(API\SpanKind::KIND_CONSUMER);
        $span->end();
        $span->end();
        $this->assertTrue($span->hasEnded());
        $this->spanProcessor
            ->shouldHaveReceived('onEnd')
            ->once();
    }

    public function test_get_start_epoch_nanos(): void
    {
        $span = $this->createTestSpan(API\SpanKind::KIND_INTERNAL);
        $this->assertSame(self::START_EPOCH, $span->getStartEpochNanos());
    }

    /**
     * @group trace-compliance
     */
    public function test_get_current_span_set_span(): void
    {
        $span = Span::wrap(SpanContext::getInvalid());

        $scope = $span->activate();

        $this->assertSame(
            $span,
            Span::getCurrent()
        );

        $scope->detach();
    }

    public function test_get_span_default_context(): void
    {
        $span = Span::fromContext(Context::getRoot());

        $this->assertSame(
            $span,
            Span::getInvalid()
        );
    }

    public function test_get_span_explicit_context(): void
    {
        $span = Span::fromContext(Context::getRoot());

        $this->assertSame(
            $span,
            Span::fromContext(
                Context::getRoot()->withContextValue($span)
            )
        );
    }

    public function test_in_process_context(): void
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

    /**
     * @group trace-compliance
     */
    public function test_nothing_changes_after_end(): void
    {
        $span = $this->createTestSpan();
        $span->end();

        // Ensure adding/updating fields after end noop.
        $this->spanDoWork($span, API\StatusCode::STATUS_ERROR, 'ERR');

        $this->assertSpanData(
            $span->toSpanData(),
            Attributes::create([]),
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

    /**
     * @group trace-compliance
     * @covers \OpenTelemetry\SDK\Trace\Span::isRecording
     */
    public function test_to_span_data_active_span(): void
    {
        $span = $this->createTestSpan();

        $this->assertFalse($span->hasEnded());
        $this->spanDoWork($span);

        $this->assertSpanData(
            $span->toSpanData(),
            $this->expectedAttributes,
            [new Event('event2', self::START_EPOCH + ClockInterface::NANOS_PER_SECOND, Attributes::create([]))],
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

    /**
     * @group trace-compliance
     */
    public function test_to_span_data_ended_span(): void
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
            [new Event('event2', self::START_EPOCH + ClockInterface::NANOS_PER_SECOND, Attributes::create([]))],
            [$this->link],
            self::NEW_SPAN_NAME,
            self::START_EPOCH,
            $this->testClock->now(),
            API\StatusCode::STATUS_ERROR,
            true
        );
    }

    public function test_to_span_data_root_span(): void
    {
        $span = $this->createTestRootSpan();
        $this->spanDoWork($span);
        $span->end();

        $this->assertFalse($span->getParentContext()->isValid());
        $this->assertFalse(SpanContextValidator::isValidSpanId($span->toSpanData()->getParentSpanId()));
    }

    public function test_to_span_data_child_span(): void
    {
        $span = $this->createTestSpan();
        $this->spanDoWork($span);
        $span->end();

        $this->assertTrue($span->getParentContext()->isValid());
        $this->assertSame($this->traceId, $span->getParentContext()->getTraceId());
        $this->assertSame($this->parentSpanId, $span->getParentContext()->getSpanId());
        $this->assertSame($this->parentSpanId, $span->toSpanData()->getParentSpanId());
    }

    public function test_to_span_data_initial_attributes(): void
    {
        $span = $this->createTestSpanWithAttributes(self::ATTRIBUTES);
        $span->setAttribute('another_key', 'another_value');
        $span->end();

        $spanData = $span->toSpanData();
        $this->assertSame(count(self::ATTRIBUTES) + 1, $spanData->getAttributes()->count());
        $this->assertSame(0, $spanData->getAttributes()->getDroppedAttributesCount());
    }

    /**
     * @group trace-compliance
     */
    public function test_to_span_data_is_immutable(): void
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

    public function test_to_span_data_status(): void
    {
        $span = $this->createTestSpan(API\SpanKind::KIND_CONSUMER);
        $this->testClock->advanceSeconds();
        $this->assertSame(StatusData::unset(), $span->toSpanData()->getStatus());
        $span->setStatus(API\StatusCode::STATUS_ERROR, 'ERR');
        $this->assertEquals(StatusData::create(API\StatusCode::STATUS_ERROR, 'ERR'), $span->toSpanData()->getStatus());
        $span->end();
        $this->assertEquals(StatusData::create(API\StatusCode::STATUS_ERROR, 'ERR'), $span->toSpanData()->getStatus());
    }

    public function test_to_span_data_kind(): void
    {
        $span = $this->createTestSpan(API\SpanKind::KIND_SERVER);
        $this->assertSame(API\SpanKind::KIND_SERVER, $span->toSpanData()->getKind());
        $span->end();
    }

    public function test_get_kind(): void
    {
        $span = $this->createTestSpan(API\SpanKind::KIND_SERVER);
        $this->assertSame(API\SpanKind::KIND_SERVER, $span->getKind());
        $span->end();
    }

    public function test_get_attribute(): void
    {
        $span = $this->createTestSpanWithAttributes(self::ATTRIBUTES);
        $this->assertSame(3.14, $span->getAttribute('float_attribute'));
        $span->end();
    }

    public function test_get_instrumentation_scope_info(): void
    {
        $span = $this->createTestSpanWithAttributes(self::ATTRIBUTES);
        $this->assertSame($this->instrumentationScope, $span->getInstrumentationScope());
        $span->end();
    }

    /**
     * @group trace-compliance
     */
    public function test_update_span_name(): void
    {
        $span = $this->createTestRootSpan();
        $this->assertSame(self::SPAN_NAME, $span->getName());
        $span->updateName(self::NEW_SPAN_NAME);
        $this->assertSame(self::NEW_SPAN_NAME, $span->getName());
        $span->end();
    }

    public function test_get_duration_active_span(): void
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

    public function test_get_duration_ended_span(): void
    {
        $span = $this->createTestSpan();
        $this->testClock->advanceSeconds();
        $span->end();

        $elapsedNanos = $this->testClock->now() - self::START_EPOCH;
        $this->assertSame($elapsedNanos, $span->getDuration());
        $this->testClock->advanceSeconds();
        $this->assertSame($elapsedNanos, $span->getDuration());
    }

    /**
     * @group trace-compliance
     * @covers \OpenTelemetry\SDK\Trace\Span::setAttributes
     */
    public function test_set_attributes(): void
    {
        $span = $this->createTestRootSpan();

        $attributes = [
            'string' => 'str',
            'empty_str' => '',
            'null' => null,
            'str_array' => ['a', 'b'],
        ];

        $span->setAttributes($attributes);
        $span->end();

        $attributes = $span->toSpanData()->getAttributes();
        $this->assertSame('str', $attributes->get('string'));
        $this->assertSame('', $attributes->get('empty_str'));
        $this->assertNull($attributes->get('null'));
        $this->assertSame(['a', 'b'], $attributes->get('str_array'));
    }

    public function test_set_attributes_overrides_attribute(): void
    {
        $span = $this->createTestSpanWithAttributes(self::ATTRIBUTES);
        $this->assertFalse($span->toSpanData()->getAttributes()->get('bool_attribute'));
        $span->setAttributes(['bool_attribute' => true]);
        $this->assertTrue($span->toSpanData()->getAttributes()->get('bool_attribute'));
    }

    public function test_set_attributes_empty(): void
    {
        $span = $this->createTestRootSpan();
        $span->setAttributes([]);
        $this->assertEmpty($span->toSpanData()->getAttributes());
    }

    /**
     * @dataProvider nonHomogeneousArrayProvider
     */
    public function test_set_attribute_drops_non_homogeneous_array(array $values): void
    {
        $this->logWriter->expects($this->once())
            ->method('write')
            ->with(
                $this->equalTo('warning'),
                $this->stringContains('non-homogeneous')
            );
        $span = $this->createTestRootSpan();
        $span->setAttribute('attr', $values);
        $this->assertNull($span->getAttribute('attr'));
    }

    public static function nonHomogeneousArrayProvider(): array
    {
        return [
            [[true, 'foo']],
            [['foo', false]],
            [['foo', 'bar', 3]],
            [[5, 3.14, true]],
            [['one' => 1, 'two' => true]],
        ];
    }

    /**
     * @dataProvider homogeneousArrayProvider
     */
    public function test_set_attribute_with_homogeneous_array(array $values): void
    {
        $span = $this->createTestRootSpan();
        $span->setAttribute('attr', $values);
        $this->assertIsArray($span->getAttribute('attr'));
    }

    public static function homogeneousArrayProvider(): array
    {
        return [
            'booleans' => [[true, false, true]],
            'strings' => [['foo', 'false', 'bar']],
            'int and double' => [[3, 3.14159]],
            'integers' => [[3, 1, 5]],
            'doubles' => [[1.25, 3.11]],
            'strings with non-numeric keys' => [['foo' => 'foo', 'bar' => 'bar']],
        ];
    }

    /**
     * @group trace-compliance
     * @covers \OpenTelemetry\SDK\Trace\Span::addEvent
     */
    public function test_add_event(): void
    {
        $span = $this->createTestRootSpan();
        $span->addEvent('event1');
        $span->addEvent('event2', ['key1' => 1]);
        $span->addEvent('event3', [], TimeUtil::secondsToNanos(10));

        $span->end();

        $events = $span->toSpanData()->getEvents();
        $this->assertCount(3, $events);
        $idx = 0;

        $this->assertEvent($events[$idx++], 'event1', Attributes::create([]), self::START_EPOCH);
        $this->assertEvent($events[$idx++], 'event2', Attributes::create(['key1' => 1]), self::START_EPOCH);
        $this->assertEvent($events[$idx], 'event3', Attributes::create([]), TimeUtil::secondsToNanos(10));
    }

    public function test_add_event_attribute_length(): void
    {
        $maxLength = 25;

        $strVal = str_repeat('a', $maxLength);
        $tooLongStrVal = "{$strVal}{$strVal}";

        $span = $this->createTestSpan(API\SpanKind::KIND_INTERNAL, (new SpanLimitsBuilder())->setAttributeValueLengthLimit($maxLength)->build());

        $span->addEvent(
            'event',
            Attributes::create([
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

    /**
     * @group trace-compliance
     */
    public function test_record_exception(): void
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
            Attributes::create([
                'exception.type' => 'Exception',
                'exception.message' => 'ERR',
                'exception.stacktrace' => StackTraceFormatter::format($exception),
            ]),
            $event->getAttributes()
        );
    }

    public function test_record_exception_additional_attributes(): void
    {
        $exception = new Exception('ERR');
        $span = $this->createTestRootSpan();

        $this->testClock->advance(1000);
        $timestamp = $this->testClock->now();

        $span->recordException($exception, [
            'foo' => 'bar',
        ]);

        $this->assertCount(1, $events = $span->toSpanData()->getEvents());
        $event = $events[0];
        $this->assertSame('exception', $event->getName());
        $this->assertSame($timestamp, $event->getEpochNanos());
        $this->assertEquals(
            Attributes::create([
                'exception.type' => 'Exception',
                'exception.message' => 'ERR',
                'exception.stacktrace' => StackTraceFormatter::format($exception),
                'foo' => 'bar',
            ]),
            $event->getAttributes()
        );
    }

    public function test_attribute_length(): void
    {
        $maxLength = 25;

        $strVal = str_repeat('a', $maxLength);
        $tooLongStrVal = "{$strVal}{$strVal}";

        $span = $this->createTestSpan(
            API\SpanKind::KIND_INTERNAL,
            (new SpanLimitsBuilder())->setAttributeValueLengthLimit($maxLength)->build(),
            null,
            Attributes::create([
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

    /**
     * @group trace-compliance
     */
    public function test_dropping_attributes(): void
    {
        $maxNumberOfAttributes = 8;
        $span = $this->createTestSpan(API\SpanKind::KIND_INTERNAL, (new SpanLimitsBuilder())->setAttributeCountLimit($maxNumberOfAttributes)->build());

        foreach (range(1, $maxNumberOfAttributes * 2) as $idx) {
            $span->setAttribute("str_attribute_{$idx}", $idx);
        }

        $spanData = $span->toSpanData();

        $this->assertCount($maxNumberOfAttributes, $spanData->getAttributes());
        $this->assertSame(8, $spanData->getAttributes()->getDroppedAttributesCount());

        $span->end();
        $spanData = $span->toSpanData();

        $this->assertCount($maxNumberOfAttributes, $spanData->getAttributes());
        $this->assertSame(8, $spanData->getAttributes()->getDroppedAttributesCount());
    }

    public function test_dropping_attributes_provided_via_span_builder(): void
    {
        $maxNumberOfAttributes = 8;

        $attributesBuilder = Attributes::factory()->builder();

        foreach (range(1, $maxNumberOfAttributes * 2) as $idx) {
            $attributesBuilder["str_attribute_{$idx}"] = $idx;
        }

        $span = $this->createTestSpan(
            API\SpanKind::KIND_INTERNAL,
            (new SpanLimitsBuilder())->setAttributeCountLimit($maxNumberOfAttributes)->build(),
            null,
            $attributesBuilder->build(),
        );

        $spanData = $span->toSpanData();

        $this->assertCount($maxNumberOfAttributes, $spanData->getAttributes());
        $this->assertSame(8, $spanData->getAttributes()->getDroppedAttributesCount());

        $span->end();
        $spanData = $span->toSpanData();

        $this->assertCount($maxNumberOfAttributes, $spanData->getAttributes());
        $this->assertSame(8, $spanData->getAttributes()->getDroppedAttributesCount());
    }

    public function test_dropping_events(): void
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

    /**
    * @group trace-compliance
    */
    public function test_set_attributes_merges_attributes(): void
    {
        $span = $this->createTestRootSpan();

        $attributes = [
            'string' => 'str_val',
            'empty_key' => '',
            'str_array' => ['f', 'b'],
        ];

        $span->setAttribute('str_array', ['a', 'b']);
        $span->setAttribute('string', 'str');

        $span->setAttributes($attributes);
        $span->end();

        $attributes = $span->toSpanData()->getAttributes();
        $this->assertSame('str_val', $attributes->get('string'));
        $this->assertSame('', $attributes->get('empty_key'));
        $this->assertSame(['f', 'b'], $attributes->get('str_array'));
    }

    /**
     * @group trace-compliance
     */
    public function test_add_event_order_preserved(): void
    {
        $span = $this->createTestRootSpan();
        $span->addEvent('a');
        $span->addEvent('b');
        $span->addEvent('c', ['key' => 2]);

        $span->end();

        $events = $span->toSpanData()->getEvents();

        $this->assertEvent($events[0], 'a', Attributes::create([]), self::START_EPOCH);
        $this->assertEvent($events[1], 'b', Attributes::create([]), self::START_EPOCH);
        $this->assertEvent($events[2], 'c', Attributes::create(['key' => 2]), self::START_EPOCH);
    }

    /**
     * @dataProvider statusCodeProvider
     * @group trace-compliance
     * @psalm-param StatusCode::STATUS_* $code
     *
     * When span status is set to Ok it SHOULD be considered final and any further attempts to change it SHOULD be ignored.
     */
    public function test_set_status_after_ok_is_ignored(string $code): void
    {
        $span = $this->createTestRootSpan();
        $span->setStatus(API\StatusCode::STATUS_OK);
        $this->assertSame(API\StatusCode::STATUS_OK, $span->toSpanData()->getStatus()->getCode());
        $span->setStatus($code);
        $this->assertNotSame($code, $span->toSpanData()->getStatus()->getCode(), 'update after Ok was ignored');
        $span->setStatus(API\StatusCode::STATUS_OK);
        $this->assertSame(API\StatusCode::STATUS_OK, $span->toSpanData()->getStatus()->getCode());
    }

    public static function statusCodeProvider(): array
    {
        return [
            [API\StatusCode::STATUS_UNSET],
            [API\StatusCode::STATUS_ERROR],
        ];
    }

    /**
     * @group trace-compliance
     */
    public function test_can_set_status_to_ok_after_error(): void
    {
        $span = $this->createTestRootSpan();
        $span->setStatus(API\StatusCode::STATUS_ERROR);
        $this->assertSame(API\StatusCode::STATUS_ERROR, $span->toSpanData()->getStatus()->getCode());
    }

    private function createTestRootSpan(): Span
    {
        return $this
            ->createTestSpan(
                API\SpanKind::KIND_INTERNAL,
                null,
                SpanContextValidator::INVALID_SPAN
            );
    }

    /**
     * @psalm-param API\SpanKind::KIND_* $kind
     * @param list<LinkInterface> $links
     */
    private function createTestSpan(
        int $kind = API\SpanKind::KIND_INTERNAL,
        SpanLimits $spanLimits = null,
        string $parentSpanId = null,
        iterable $attributes = [],
        array $links = [],
    ): Span {
        $parentSpanId = $parentSpanId ?? $this->parentSpanId;
        $spanLimits = $spanLimits ?? (new SpanLimitsBuilder())->build();
        $links = $links ?: [$this->link];

        $span = Span::startSpan(
            self::SPAN_NAME,
            $this->spanContext,
            $this->instrumentationScope,
            $kind,
            $parentSpanId ? Span::wrap(SpanContext::create($this->traceId, $parentSpanId)) : Span::getInvalid(),
            Context::getRoot(),
            $spanLimits,
            $this->spanProcessor,
            $this->resource,
            $spanLimits->getAttributesFactory()->builder($attributes),
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
                Attributes::create($attributes),
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
        EventInterface $event,
        string $expectedName,
        AttributesInterface $expectedAttributes,
        int $expectedEpochNanos,
    ): void {
        $this->assertSame($expectedName, $event->getName());
        $this->assertEquals($expectedAttributes, $event->getAttributes());
        $this->assertSame($expectedEpochNanos, $event->getEpochNanos());
    }

    /**
     * @param list<EventInterface> $events
     * @param list<LinkInterface> $links
     * @psalm-param API\StatusCode::STATUS_* $status
     */
    private function assertSpanData(
        SpanDataInterface $spanData,
        AttributesInterface $attributes,
        array $events,
        array $links,
        string $spanName,
        int $startEpochNanos,
        int $endEpochNanos,
        string $status,
        bool $hasEnded,
    ): void {
        $this->assertSame($spanName, $spanData->getName());
        $this->assertSame($this->traceId, $spanData->getTraceId());
        $this->assertSame($this->spanId, $spanData->getSpanId());
        $this->assertSame($this->parentSpanId, $spanData->getParentSpanId());
        $this->assertNull($spanData->getContext()->getTraceState());
        $this->assertSame($this->resource, $spanData->getResource());
        $this->assertSame($this->instrumentationScope, $spanData->getInstrumentationScope());
        $this->assertEquals($events, $spanData->getEvents());
        $this->assertEquals($links, $spanData->getLinks());
        $this->assertSame($startEpochNanos, $spanData->getStartEpochNanos());
        $this->assertSame($endEpochNanos, $spanData->getEndEpochNanos());
        $this->assertSame($status, $spanData->getStatus()->getCode());
        $this->assertSame($hasEnded, $spanData->hasEnded());
        $this->assertEquals($attributes, $spanData->getAttributes());
    }
}
