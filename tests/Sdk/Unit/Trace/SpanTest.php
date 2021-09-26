<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Trace;

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
use OpenTelemetry\Sdk\Trace\Links;
use OpenTelemetry\Sdk\Trace\RandomIdGenerator;
use OpenTelemetry\Sdk\Trace\Span;
use OpenTelemetry\Sdk\Trace\SpanContext;
use OpenTelemetry\Sdk\Trace\SpanData;
use OpenTelemetry\Sdk\Trace\SpanProcessor;
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
    private API\Clock $testClock;

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
            new Links(),
            self::SPAN_NAME,
            self::START_EPOCH,
            self::START_EPOCH,
            API\StatusCode::STATUS_UNSET,
            true
        );
    }

    // endregion SDK

    /**
     * @param API\SpanKind::KIND_* $kind
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
        $links = $links ?? new Links();

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
            ->with($span, Context::getRoot());

        return $span;
    }

    /** @param API\StatusCode::STATUS_* $status */
    private function spanDoWork(Span $span, ?string $status = null, ?string $description = null): void
    {
        $span->setAttribute('some_string_key', 'some_string_value');
        $this->testClock->advance($this->secondsToNanoseconds(1));
        $span->addEvent('event2');
        $this->testClock->advance($this->secondsToNanoseconds(1));
        $span->updateName(self::NEW_SPAN_NAME);
        if ($status) {
            $span->setStatus($status, $description);
        }
    }

    /** @param API\StatusCode::STATUS_* $status */
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

    private function secondsToNanoseconds(int $seconds): int
    {
        return $seconds * 1000000000;
    }
}
