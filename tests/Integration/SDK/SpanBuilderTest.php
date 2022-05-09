<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Integration\SDK\Trace;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use OpenTelemetry\API\Trace as API;
use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextStorage;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Trace\Link;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOffSampler;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use OpenTelemetry\SDK\Trace\SamplingResult;
use OpenTelemetry\SDK\Trace\Span;
use OpenTelemetry\SDK\Trace\SpanLimitsBuilder;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;
use OpenTelemetry\SDK\Trace\TracerProvider;
use function range;
use function str_repeat;

/**
 * @coversNothing
 */
class SpanBuilderTest extends MockeryTestCase
{
    private const SPAN_NAME = 'span_name';

    private API\TracerInterface $tracer;
    private API\SpanContextInterface $sampledSpanContext;

    /** @var MockInterface&SpanProcessorInterface  */
    private $spanProcessor;

    protected function setUp(): void
    {
        $this->spanProcessor = Mockery::spy(SpanProcessorInterface::class);
        $this->tracer = (new TracerProvider($this->spanProcessor))->getTracer('SpanBuilderTest');

        $this->sampledSpanContext = SpanContext::create(
            '12345678876543211234567887654321',
            '8765432112345678',
            API\SpanContextInterface::TRACE_FLAG_SAMPLED,
        );
    }

    /**
     * @group trace-compliance
     */
    public function test_add_link(): void
    {
        /** @var Span $span */
        $span = $this
            ->tracer
            ->spanBuilder(self::SPAN_NAME)
            ->addLink($this->sampledSpanContext)
            ->addLink($this->sampledSpanContext, new Attributes())
            ->startSpan();

        $this->assertCount(2, $span->toSpanData()->getLinks());
    }

    public function test_add_link_invalid(): void
    {
        /** @var Span $span */
        $span = $this
            ->tracer
            ->spanBuilder(self::SPAN_NAME)
            ->addLink(Span::getInvalid()->getContext())
            ->addLink(Span::getInvalid()->getContext(), new Attributes())
            ->startSpan();

        $this->assertEmpty($span->toSpanData()->getLinks());
        $span->end();
    }

    public function test_add_link_dropping_links(): void
    {
        $maxNumberOfLinks = 8;
        $spanBuilder = (new TracerProvider([], null, null, (new SpanLimitsBuilder())->setLinkCountLimit($maxNumberOfLinks)->build()))
            ->getTracer('test')
            ->spanBuilder(self::SPAN_NAME);

        for ($idx = 0; $idx < $maxNumberOfLinks * 2; $idx++) {
            $spanBuilder->addLink($this->sampledSpanContext);
        }

        /** @var Span $span */
        $span = $spanBuilder->startSpan();

        $spanData = $span->toSpanData();
        $links = $spanData->getLinks();

        $this->assertCount($maxNumberOfLinks, $links);
        $this->assertSame(8, $spanData->getTotalDroppedLinks());

        for ($idx = 0; $idx < $maxNumberOfLinks; $idx++) {
            $this->assertEquals(new Link($this->sampledSpanContext), $links[$idx]);
        }

        $span->end();
    }

    public function test_add_link_truncate_link_attributes(): void
    {
        /** @var Span $span */
        $span = (new TracerProvider([], null, null, (new SpanLimitsBuilder())->setAttributePerLinkCountLimit(1)->build()))
            ->getTracer('test')
            ->spanBuilder(self::SPAN_NAME)
            ->addLink(
                $this->sampledSpanContext,
                new Attributes([
                    'key0' => 0,
                    'key1' => 1,
                    'key2' => 2,
                ])
            )
            ->startSpan();

        $this->assertCount(1, $span->toSpanData()->getLinks());
        $this->assertCount(1, $span->toSpanData()->getLinks()[0]->getAttributes());
    }

    public function test_add_link_truncate_link_attribute_value(): void
    {
        $maxLength = 25;

        $strVal = str_repeat('a', $maxLength);
        $tooLongStrVal = "${strVal}${strVal}";

        /** @var Span $span */
        $span = (new TracerProvider([], null, null, (new SpanLimitsBuilder())->setAttributeValueLengthLimit($maxLength)->build()))
            ->getTracer('test')
            ->spanBuilder(self::SPAN_NAME)
            ->addLink(
                $this->sampledSpanContext,
                new Attributes([
                    'string' => $tooLongStrVal,
                    'bool' => true,
                    'string_array' => [$strVal, $tooLongStrVal],
                    'int_array' => [1, 2],
                ])
            )
            ->startSpan();

        $attrs = $span->toSpanData()->getLinks()[0]->getAttributes();
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
    }

    /**
     * @group trace-compliance
     */
    public function test_add_link_no_effect_after_start_span(): void
    {
        $spanBuilder = $this->tracer->spanBuilder(self::SPAN_NAME);

        /** @var Span $span */
        $span = $spanBuilder
            ->addLink($this->sampledSpanContext)
            ->startSpan();

        $this->assertCount(1, $span->toSpanData()->getLinks());

        $spanBuilder
            ->addLink(
                SpanContext::create(
                    '00000000000004d20000000000001a85',
                    '0000000000002694',
                    API\SpanContextInterface::TRACE_FLAG_SAMPLED
                )
            );

        $this->assertCount(1, $span->toSpanData()->getLinks());
    }

    /**
     * @group trace-compliance
     */
    public function test_set_attribute(): void
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
    }

    /**
     * @group trace-compliance
     */
    public function test_set_attribute_no_effect_after_end(): void
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

        $span->setAttribute('doo', 'baz');

        $this->assertSame(2, $attributes->count());
        $this->assertFalse($attributes->hasAttribute('doo'));
    }

    /**
     * @group trace-compliance
     */
    // public function test_set_attribute_empty_string_value_is_set(): void
    // {
    //     /** @var Span $span */
    //     $span = $this
    //         ->tracer
    //         ->spanBuilder(self::SPAN_NAME)
    //         ->setAttribute('nil', null)
    //         ->setAttribute('empty-string', '')
    //         ->startSpan();

    //     $attributes = $span->toSpanData()->getAttributes();
    //     $this->assertSame(1, $attributes->count());
    //     $this->assertSame('', $attributes->get('empty-string'));
    //     $this->assertNull($attributes->get('nil'));

    //     $span->end();
    // }

    /**
     * @group trace-compliance
     */
    public function test_set_attribute_only_null_string_value_should_not_be_set(): void
    {
        /** @var Span $span */
        $span = $this
            ->tracer
            ->spanBuilder(self::SPAN_NAME)
            ->setAttribute('nil', null)
            ->startSpan();

        $attributes = $span->toSpanData()->getAttributes();
        $this->assertEmpty($span->toSpanData()->getAttributes());
        $this->assertNull($attributes->get('nil'));
    }

    /**
     * @group trace-compliance
     */
    public function test_set_attribute_no_effect_after_start_span(): void
    {
        $spanBuilder = $this->tracer->spanBuilder(self::SPAN_NAME);

        /** @var Span $span */
        $span = $spanBuilder
            ->setAttribute('foo', 'bar')
            ->setAttribute('bar', 123)
            ->startSpan();

        $attributes = $span->toSpanData()->getAttributes();
        $this->assertSame(2, $attributes->count());

        $spanBuilder
            ->setAttribute('bar1', 77);

        $attributes = $span->toSpanData()->getAttributes();
        $this->assertSame(2, $attributes->count());
        $this->assertFalse($attributes->hasAttribute('bar1'));
    }

    public function test_set_attribute_dropping(): void
    {
        $maxNumberOfAttributes = 8;
        $spanBuilder = (new TracerProvider(
            null,
            null,
            null,
            (new SpanLimitsBuilder())->setAttributeCountLimit($maxNumberOfAttributes)->build()
        ))->getTracer('test')->spanBuilder(self::SPAN_NAME);

        foreach (range(1, $maxNumberOfAttributes * 2) as $idx) {
            $spanBuilder->setAttribute("str_attribute_${idx}", $idx);
        }

        /** @var Span $span */
        $span = $spanBuilder->startSpan();
        $attributes = $span->toSpanData()->getAttributes();

        $this->assertCount($maxNumberOfAttributes, $attributes);

        foreach (range(1, $maxNumberOfAttributes) as $idx) {
            $this->assertSame($idx, $attributes->get("str_attribute_${idx}"));
        }
    }

    public function test_add_attributes_via_sampler(): void
    {
        $sampler = new class() implements SamplerInterface {
            public function shouldSample(
                Context $parentContext,
                string $traceId,
                string $spanName,
                int $spanKind,
                ?AttributesInterface $attributes = null,
                array $links = []
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

    public function test_set_attributes(): void
    {
        $attributes = new Attributes(['id' => 1, 'foo' => 'bar']);

        /** @var Span $span */
        $span = $this->tracer->spanBuilder(self::SPAN_NAME)->setAttributes($attributes)->startSpan();

        $attributes = $span->toSpanData()->getAttributes();

        $this->assertSame(2, $attributes->count());
        $this->assertSame('bar', $attributes->get('foo'));
        $this->assertSame(1, $attributes->get('id'));
    }

    /**
     * @group trace-compliance
     */
    public function test_set_attributes_merges_attributes_correctly(): void
    {
        $attributes = new Attributes(['id' => 2, 'foo' => 'bar', 'key' => 'val']);

        /** @var Span $span */
        $span = $this
            ->tracer
            ->spanBuilder(self::SPAN_NAME)
            ->setAttribute('key2', 'val2')
            ->setAttribute('key1', 'val1')
            ->setAttributes($attributes)
            ->startSpan();

        $attributes = $span->toSpanData()->getAttributes();

        $this->assertSame(5, $attributes->count());
        $this->assertSame('bar', $attributes->get('foo'));
        $this->assertSame(2, $attributes->get('id'));
        $this->assertSame('val', $attributes->get('key'));
        $this->assertSame('val2', $attributes->get('key2'));
        $this->assertSame('val1', $attributes->get('key1'));
    }

    public function test_set_attributes_overrides_values(): void
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
    }

    public function test_is_recording_default(): void
    {
        /** @var Span $span */
        $span = $this->tracer->spanBuilder(self::SPAN_NAME)->startSpan();
        $this->assertTrue($span->isRecording());
        $span->end();
    }

    public function test_is_recording_sampler(): void
    {
        /** @var Span $span */
        $span = (new TracerProvider([], new AlwaysOffSampler()))
            ->getTracer('test')
            ->spanBuilder(self::SPAN_NAME)
            ->startSpan();

        $this->assertFalse($span->isRecording());
        $span->end();
    }

    public function test_get_kind_default(): void
    {
        /** @var Span $span */
        $span = $this->tracer->spanBuilder(self::SPAN_NAME)->startSpan();
        $this->assertSame(API\SpanKind::KIND_INTERNAL, $span->getKind());
        $span->end();
    }

    public function test_get_kind(): void
    {
        /** @var Span $span */
        $span = $this->tracer->spanBuilder(self::SPAN_NAME)->setSpanKind(API\SpanKind::KIND_CONSUMER)->startSpan();
        $this->assertSame(API\SpanKind::KIND_CONSUMER, $span->getKind());
        $span->end();
    }

    public function test_start_timestamp(): void
    {
        /** @var Span $span */
        $span = $this->tracer->spanBuilder(self::SPAN_NAME)->setStartTimestamp(123)->startSpan();
        $span->end();
        $this->assertSame(123, $span->toSpanData()->getStartEpochNanos());
    }

    public function test_set_no_parent(): void
    {
        $parentSpan = $this->tracer->spanBuilder(self::SPAN_NAME)->startSpan();
        $parentScope = $parentSpan->activate();

        /** @var Span $span */
        $span = $this->tracer->spanBuilder(self::SPAN_NAME)->setNoParent()->startSpan();

        $this->assertNotSame(
            $span->getContext()->getTraceId(),
            $parentSpan->getContext()->getTraceId()
        );

        $this
            ->spanProcessor
            ->shouldHaveReceived('onStart')
            ->with($span, Context::getRoot())
            ->once();

        /** @var Span $spanNoParent */
        $spanNoParent = $this
            ->tracer
            ->spanBuilder(self::SPAN_NAME)
            ->setNoParent()
            ->setParent(Context::getCurrent())
            ->setNoParent()
            ->startSpan();

        $this->assertNotSame($span->getContext()->getTraceId(), $spanNoParent->getContext()->getTraceId());

        $this
            ->spanProcessor
            ->shouldHaveReceived('onStart')
            ->with($spanNoParent, Context::getRoot())
            ->once();

        $spanNoParent->end();
        $span->end();
        $parentScope->detach();
        $parentSpan->end();
    }

    public function test_set_no_parent_override(): void
    {
        $parentSpan = $this->tracer->spanBuilder(self::SPAN_NAME)->startSpan();
        $parentContext = Context::getCurrent()->withContextValue($parentSpan);

        /** @var Span $span */
        $span = $this->tracer->spanBuilder(self::SPAN_NAME)->setNoParent()->setParent($parentContext)->startSpan();

        $this
            ->spanProcessor
            ->shouldHaveReceived('onStart')
            ->with($span, $parentContext)
            ->once();

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

        $this
            ->spanProcessor
            ->shouldHaveReceived('onStart')
            ->with($span2, $parentContext2)
            ->once();

        $this->assertSame(
            $span2->getContext()->getTraceId(),
            $parentSpan->getContext()->getTraceId()
        );

        $span2->end();
        $span->end();
        $parentSpan->end();
    }

    public function test_set_parent_empty_context(): void
    {
        $emptyContext = Context::getCurrent();
        $parentSpan = $this->tracer->spanBuilder(self::SPAN_NAME)->startSpan();
        $parentScope = $parentSpan->activate();

        /** @var Span $span */
        $span = $this->tracer->spanBuilder(self::SPAN_NAME)->setParent($emptyContext)->startSpan();

        $this
            ->spanProcessor
            ->shouldHaveReceived('onStart')
            ->with($span, $emptyContext)
            ->once();

        $this->assertNotSame(
            $span->getContext()->getTraceId(),
            $parentSpan->getContext()->getTraceId()
        );
        $this->assertNotSame(
            $span->toSpanData()->getParentSpanId(),
            $parentSpan->getContext()->getSpanId()
        );

        $span->end();
        $parentScope->detach();
        $parentSpan->end();
    }

    public function test_set_parent_current_span(): void
    {
        $parentSpan = $this->tracer->spanBuilder(self::SPAN_NAME)->startSpan();
        $parentScope = $parentSpan->activate();
        $implicitContext = Context::getCurrent();

        /** @var Span $span */
        $span = $this->tracer->spanBuilder(self::SPAN_NAME)->startSpan();

        $this
            ->spanProcessor
            ->shouldHaveReceived('onStart')
            ->with($span, $implicitContext)
            ->once();

        $this->assertSame(
            $span->getContext()->getTraceId(),
            $parentSpan->getContext()->getTraceId()
        );
        $this->assertSame(
            $span->toSpanData()->getParentSpanId(),
            $parentSpan->getContext()->getSpanId()
        );

        $span->end();
        $parentScope->detach();
        $parentSpan->end();
    }

    public function test_set_parent_invalid_context(): void
    {
        $parentSpan = Span::getInvalid();

        $parentContext = Context::getCurrent()->withContextValue($parentSpan);

        /** @var Span $span */
        $span = $this->tracer->spanBuilder(self::SPAN_NAME)->setParent($parentContext)->startSpan();

        $this
            ->spanProcessor
            ->shouldHaveReceived('onStart')
            ->with($span, $parentContext)
            ->once();

        $this->assertNotSame(
            $span->getContext()->getTraceId(),
            $parentSpan->getContext()->getTraceId()
        );

        $this->assertFalse(SpanContext::isValidSpanId($span->toSpanData()->getParentSpanId()));

        $span->end();
        $parentSpan->end();
    }

    /**
     * @group concurrency
     */
    public function test_custom_storage_does_not_use_shared_storage(): void
    {
        $sharedStorage = Context::storage();
        $storage = ContextStorage::create();
        $rootShared = $this->tracer->spanBuilder('root.shared')->startSpan();
        $rootShared->activate();
        $rootCustom = $this->tracer->spanBuilder('root.custom')->setStorage($storage)->startSpan();
        $rootCustom->activate();

        $this
            ->spanProcessor
            ->shouldHaveReceived('onStart')
            ->with($rootShared, $sharedStorage->current())
            ->once();
        $this
            ->spanProcessor
            ->shouldHaveReceived('onStart')
            ->with($rootCustom, $storage->current());
        $this->assertNotSame($storage->current(), $sharedStorage->current());

        $this->assertNotSame($sharedStorage->current(), $storage->current(), 'current context is different in each storage');

        $childOne = $this->tracer->spanBuilder('one')->startSpan(); //child span of root.shared
        $childTwo = $this->tracer->spanBuilder('two')->setStorage($storage)->startSpan(); //child span of root.custom
        $this
            ->spanProcessor
            ->shouldHaveReceived('onStart')
            ->with($childOne, $sharedStorage->current());
        $this
            ->spanProcessor
            ->shouldHaveReceived('onStart')
            ->with($childTwo, $storage->current());
    }
}
