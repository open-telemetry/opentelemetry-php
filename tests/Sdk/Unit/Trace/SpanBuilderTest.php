<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Trace;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Sdk\Trace\Attributes;
use OpenTelemetry\Sdk\Trace\Sampler;
use OpenTelemetry\Sdk\Trace\SamplingResult;
use OpenTelemetry\Sdk\Trace\Span;
use OpenTelemetry\Sdk\Trace\SpanContext;
use OpenTelemetry\Sdk\Trace\SpanProcessor;
use OpenTelemetry\Sdk\Trace\TracerProvider;
use OpenTelemetry\Trace as API;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SpanBuilderTest extends TestCase
{
    private const SPAN_NAME = 'span_name';

    /** @var API\Tracer */
    private $tracer;

    /** @var API\SpanContext */
    private $sampledSpanContext;

    /** @var MockObject&SpanProcessor */
    private $spanProcessor;

    protected function setUp(): void
    {
        $this->spanProcessor = $this->createMock(SpanProcessor::class);
        $this->tracer = (new TracerProvider($this->spanProcessor))->getTracer('SpanBuilderTest');

        $this->sampledSpanContext = SpanContext::create(
            '12345678876543211234567887654321',
            '8765432112345678',
            API\SpanContext::TRACE_FLAG_SAMPLED,
        );
    }

    public function test_addLink(): void
    {
        /** @var Span $span */
        $span = $this
            ->tracer
            ->spanBuilder(self::SPAN_NAME)
            ->addLink($this->sampledSpanContext)
            ->addLink($this->sampledSpanContext, new Attributes())
            ->startSpan();

        $this->assertSame(2, $span->toSpanData()->getLinks()->count());
        $span->end();
    }

    public function test_addLink_invalid(): void
    {
        /** @var Span $span */
        $span = $this
            ->tracer
            ->spanBuilder(self::SPAN_NAME)
            ->addLink(Span::getInvalid()->getContext())
            ->addLink(Span::getInvalid()->getContext(), new Attributes())
            ->startSpan();

        $this->assertSame(0, $span->toSpanData()->getLinks()->count());
        $span->end();
    }

    // TODO: Test truncating links & link attributes

    public function test_addLink_noEffectAfterStartSpan(): void
    {
        $spanBuilder = $this->tracer->spanBuilder(self::SPAN_NAME);

        /** @var Span $span */
        $span = $spanBuilder
            ->addLink($this->sampledSpanContext)
            ->startSpan();

        $this->assertSame(1, $span->toSpanData()->getLinks()->count());

        $spanBuilder
            ->addLink(
                SpanContext::create(
                    '00000000000004d20000000000001a85',
                    '0000000000002694',
                    API\SpanContext::TRACE_FLAG_SAMPLED
                )
            );

        $this->assertSame(1, $span->toSpanData()->getLinks()->count());

        $span->end();
    }

    public function test_setAttribute(): void
    {
        /** @var Span $span */
        $span = $this
            ->tracer
            ->spanBuilder(self::SPAN_NAME)
            ->setAttribute('foo', 'bar')
            ->setAttribute('bar', 123)
            ->setAttribute('empty-arr', [])
            ->setAttribute('int-arr', [1, 2, 3])
            ->setAttribute('nil', null)
            ->startSpan();

        $attributes = $span->toSpanData()->getAttributes();
        $this->assertSame(4, $attributes->count());

        $this->assertSame('bar', $attributes->get('foo'));
        $this->assertSame(123, $attributes->get('bar'));
        $this->assertSame([], $attributes->get('empty-arr'));
        $this->assertSame([1, 2, 3], $attributes->get('int-arr'));
        $this->assertNull($attributes->get('nil'));

        $span->end();
    }

    public function test_setAttribute_afterEnd(): void
    {
        /** @var Span $span */
        $span = $this
            ->tracer
            ->spanBuilder(self::SPAN_NAME)
            ->setAttribute('foo', 'bar')
            ->setAttribute('bar', 123)
            ->startSpan();

        $attributes = $span->toSpanData()->getAttributes();
        $this->assertSame(2, $attributes->count());
        $this->assertSame('bar', $attributes->get('foo'));
        $this->assertSame(123, $attributes->get('bar'));

        $span->end();
    }

    // TODO: Test dropping attributes over limits

    public function test_addAttributesViaSampler(): void
    {
        $sampler = new class() implements Sampler {
            public function shouldSample(
                Context $parentContext,
                string $traceId,
                string $spanName,
                int $spanKind,
                ?API\Attributes $attributes = null,
                ?API\Links $links = null
            ): SamplingResult {
                return new SamplingResult(SamplingResult::RECORD_AND_SAMPLE, new Attributes(['cat' => 'meow']));
            }

            public function getDescription(): string
            {
                return 'test';
            }
        };

        /** @var Span $span */
        $span = (new TracerProvider([], $sampler))->getTracer('test')->spanBuilder(self::SPAN_NAME)->startSpan();
        $span->end();

        $attributes = $span->toSpanData()->getAttributes();

        $this->assertSame(1, $attributes->count());
        $this->assertSame('meow', $attributes->get('cat'));
    }

    public function test_setAttributes(): void
    {
        $attributes = new Attributes(['id' => 1, 'foo' => 'bar']);

        /** @var Span $span */
        $span = $this->tracer->spanBuilder(self::SPAN_NAME)->setAttributes($attributes)->startSpan();

        $attributes = $span->toSpanData()->getAttributes();

        $this->assertSame(2, $attributes->count());
        $this->assertSame('bar', $attributes->get('foo'));
        $this->assertSame(1, $attributes->get('id'));

        $span->end();
    }

    public function test_setAttributes_overridesValues(): void
    {
        $attributes = new Attributes(['id' => 1, 'foo' => 'bar']);

        /** @var Span $span */
        $span = $this
            ->tracer
            ->spanBuilder(self::SPAN_NAME)
            ->setAttribute('id', 0)
            ->setAttribute('foo', 'baz')
            ->setAttributes($attributes)
            ->startSpan();

        $attributes = $span->toSpanData()->getAttributes();

        $this->assertSame(2, $attributes->count());
        $this->assertSame('bar', $attributes->get('foo'));
        $this->assertSame(1, $attributes->get('id'));

        $span->end();
    }

    public function test_isRecording_default(): void
    {
        /** @var Span $span */
        $span = $this->tracer->spanBuilder(self::SPAN_NAME)->startSpan();
        $this->assertTrue($span->isRecording());
        $span->end();
    }

    public function test_isRecording_sampler(): void
    {
        /** @var Span $span */
        $span = (new TracerProvider([], new Sampler\AlwaysOffSampler()))
            ->getTracer('test')
            ->spanBuilder(self::SPAN_NAME)
            ->startSpan();

        $this->assertFalse($span->isRecording());
        $span->end();
    }

    public function test_getKind_default(): void
    {
        /** @var Span $span */
        $span = $this->tracer->spanBuilder(self::SPAN_NAME)->startSpan();
        $this->assertSame(API\SpanKind::KIND_INTERNAL, $span->getKind());
        $span->end();
    }

    public function test_getKind(): void
    {
        /** @var Span $span */
        $span = $this->tracer->spanBuilder(self::SPAN_NAME)->setSpanKind(API\SpanKind::KIND_CONSUMER)->startSpan();
        $this->assertSame(API\SpanKind::KIND_CONSUMER, $span->getKind());
        $span->end();
    }

    public function test_setNoParent(): void
    {
        $parentSpan = $this->tracer->spanBuilder(self::SPAN_NAME)->startSpan();
        $parentScope = $parentSpan->activate();

        /** @var Span $span */
        $span = $this->tracer->spanBuilder(self::SPAN_NAME)->setNoParent()->startSpan();

        $this->assertNotSame(
            $span->getContext()->getTraceId(),
            $parentSpan->getContext()->getTraceId()
        );

        /** @var Span $spanNoParent */
        $spanNoParent = $this
            ->tracer
            ->spanBuilder(self::SPAN_NAME)
            ->setNoParent()
            ->setParent(Context::getCurrent())
            ->setNoParent()
            ->startSpan();

        $this->assertNotSame($span->getContext()->getTraceId(), $spanNoParent->getContext()->getTraceId());

        $spanNoParent->end();
        $span->end();
        $parentScope->close();
        $parentSpan->end();
    }

    public function test_setNoParent_override(): void
    {
        $parentSpan = $this->tracer->spanBuilder(self::SPAN_NAME)->startSpan();
        $parentContext = Context::getCurrent()->withContextValue($parentSpan);

        /** @var Span $span */
        $span = $this->tracer->spanBuilder(self::SPAN_NAME)->setNoParent()->setParent($parentContext)->startSpan();

        $this->assertSame(
            $span->getContext()->getTraceId(),
            $parentSpan->getContext()->getTraceId()
        );
        $this->assertSame(
            $span->toSpanData()->getParentSpanId(),
            $parentSpan->getContext()->getSpanId()
        );

        $parentContext2 = Context::getCurrent()->withContextValue($parentSpan);

        /** @var Span $span2 */
        $span2 = $this
            ->tracer
            ->spanBuilder(self::SPAN_NAME)
            ->setNoParent()
            ->setParent($parentContext2)
            ->startSpan();

        $this->assertSame(
            $span2->getContext()->getTraceId(),
            $parentSpan->getContext()->getTraceId()
        );

        $span2->end();
        $span->end();
        $parentSpan->end();
    }

    public function test_setParent_emptyContext(): void
    {
        $emptyContext = Context::getCurrent();
        $parentSpan = $this->tracer->spanBuilder(self::SPAN_NAME)->startSpan();
        $parentScope = $parentSpan->activate();

        /** @var Span $span */
        $span = $this->tracer->spanBuilder(self::SPAN_NAME)->setParent($emptyContext)->startSpan();

        $this->assertNotSame(
            $span->getContext()->getTraceId(),
            $parentSpan->getContext()->getTraceId()
        );
        $this->assertNotSame(
            $span->toSpanData()->getParentSpanId(),
            $parentSpan->getContext()->getSpanId()
        );

        $span->end();
        $parentScope->close();
        $parentSpan->end();
    }
}
