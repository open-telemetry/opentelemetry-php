<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace;

use OpenTelemetry\API\Trace\SpanContextInterface;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\SDK\Common\Attribute\AttributesFactoryInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScope;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Trace\IdGeneratorInterface;
use OpenTelemetry\SDK\Trace\ReadableSpanInterface;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use OpenTelemetry\SDK\Trace\SamplingResult;
use OpenTelemetry\SDK\Trace\SpanBuilder;
use OpenTelemetry\SDK\Trace\SpanLimits;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;
use OpenTelemetry\SDK\Trace\TracerSharedState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(SpanBuilder::class)]
class SpanBuilderTest extends TestCase
{
    private const TRACE_ID = 'e4a8d4e0d75c0702200af2882cb16c6b';
    private const SPAN_ID = '46701247e52c2d1b';
    private SpanBuilder $builder;
    /** @var SamplerInterface&MockObject $sampler */
    private SamplerInterface $sampler;
    /** @var IdGeneratorInterface&MockObject $idGenerator */
    private IdGeneratorInterface $idGenerator;
    /** @var SpanProcessorInterface&MockObject $spanProcessor */
    private SpanProcessorInterface $spanProcessor;
    #[\Override]
    public function setUp(): void
    {
        $instrumentationScope = new InstrumentationScope(
            's',
            '1.0',
            'https://example.com/schema',
            $this->createMock(AttributesInterface::class)
        ); //final
        $attributesFactory = $this->createMock(AttributesFactoryInterface::class);
        $spanLimits = new SpanLimits(
            $attributesFactory,
            $attributesFactory,
            $attributesFactory,
            10,
            10,
        );
        $this->sampler = $this->createMock(SamplerInterface::class);
        $this->idGenerator = $this->createMock(IdGeneratorInterface::class);
        $this->spanProcessor = $this->createMock(SpanProcessorInterface::class);
        $sharedState = new TracerSharedState(
            $this->idGenerator,
            $this->createMock(ResourceInfo::class),
            $spanLimits,
            $this->sampler,
            [$this->spanProcessor]
        );
        $this->builder = new SpanBuilder('foo', $instrumentationScope, $sharedState);
    }

    public function test_start_span(): void
    {
        $this->sampler->expects($this->once())->method('shouldSample')->willReturn(new SamplingResult(SamplingResult::RECORD_AND_SAMPLE));
        $this->idGenerator->method('generateTraceId')->willReturn(self::TRACE_ID);
        $this->idGenerator->method('generateSpanId')->willReturn(self::SPAN_ID);
        $linkContext = $this->createMock(SpanContextInterface::class);
        $linkContext->method('isValid')->willReturn(true);

        /** @var ReadableSpanInterface $span */
        $span = $this->builder
            ->setSpanKind(SpanKind::KIND_CLIENT)
            ->setAttributes(['foo' => 'bar'])
            ->addLink($linkContext, ['link-attr' => 'link-val'])
            ->setStartTimestamp(123456)
            ->startSpan();

        $this->assertSame(self::TRACE_ID, $span->toSpanData()->getTraceId());
        $this->assertSame(self::SPAN_ID, $span->toSpanData()->getSpanId());
        $this->assertSame(123456, $span->toSpanData()->getStartEpochNanos());
        $this->assertCount(1, $span->toSpanData()->getLinks());
    }
}
