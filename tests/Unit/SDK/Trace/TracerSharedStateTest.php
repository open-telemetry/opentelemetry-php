<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace;

use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Trace\IdGeneratorInterface;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use OpenTelemetry\SDK\Trace\SpanLimits;
use OpenTelemetry\SDK\Trace\SpanProcessor\MultiSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessor\NoopSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;
use OpenTelemetry\SDK\Trace\TracerSharedState;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\SDK\Trace\TracerSharedState
 */
class TracerSharedStateTest extends TestCase
{
    private IdGeneratorInterface $idGenerator;
    private ResourceInfo $resourceInfo;
    private SamplerInterface $sampler;
    private SpanLimits $spanLimits;

    protected function setUp(): void
    {
        $this->idGenerator = $this->createMock(IdGeneratorInterface::class);
        $this->resourceInfo = $this->createMock(ResourceInfo::class);
        $this->sampler = $this->createMock(SamplerInterface::class);
        $this->spanLimits = $this->createMock(SpanLimits::class);
    }

    public function test_getters(): void
    {
        $state = $this->construct();
        $this->assertSame(
            $this->idGenerator,
            $state->getIdGenerator()
        );

        $this->assertSame(
            $this->resourceInfo,
            $state->getResource()
        );

        $this->assertSame(
            $this->sampler,
            $state->getSampler()
        );

        $this->assertSame(
            $this->spanLimits,
            $state->getSpanLimits(),
        );
    }

    public function test_construct_no_span_processors(): void
    {
        $this->assertInstanceOf(
            NoopSpanProcessor::class,
            $this->construct()->getSpanProcessor()
        );
    }

    public function test_construct_one_span_processor(): void
    {
        $processor = $this->createMock(SpanProcessorInterface::class);

        $this->assertSame(
            $processor,
            $this->construct([$processor])->getSpanProcessor()
        );
    }

    public function test_construct_multiple_span_processors(): void
    {
        $processor1 = $this->createMock(SpanProcessorInterface::class);
        $processor2 = $this->createMock(SpanProcessorInterface::class);

        $this->assertInstanceOf(
            MultiSpanProcessor::class,
            $this->construct([$processor1, $processor2])->getSpanProcessor()
        );
    }

    public function test_shutdown(): void
    {
        $spanProcessor = $this->createMock(SpanProcessorInterface::class);
        $state = $this->construct([$spanProcessor]);
        $spanProcessor->expects($this->once())->method('shutdown')->willReturn(true);
        $this->assertTrue($state->shutdown());
        $this->assertTrue($state->hasShutdown());
    }

    public function test_sets_noop_as_default_span_processor(): void
    {
        $state = new TracerSharedState(
            $this->idGenerator,
            $this->resourceInfo,
            $this->spanLimits,
            $this->sampler,
            [],
        );
        $this->assertInstanceOf(NoopSpanProcessor::class, $state->getSpanProcessor());
    }

    private function construct(array $spanProcessors = []): TracerSharedState
    {
        return new TracerSharedState(
            $this->idGenerator,
            $this->resourceInfo,
            $this->spanLimits,
            $this->sampler,
            empty($spanProcessors) ? [new NoopSpanProcessor()] : $spanProcessors,
        );
    }
}
