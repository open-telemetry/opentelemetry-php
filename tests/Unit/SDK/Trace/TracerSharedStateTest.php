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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TracerSharedState::class)]
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

    public function test_shutdown(): void
    {
        $spanProcessor = $this->createMock(SpanProcessorInterface::class);
        $state = $this->construct([$spanProcessor]);
        $spanProcessor->expects($this->once())->method('shutdown')->willReturn(true);
        $this->assertTrue($state->shutdown());
        $this->assertTrue($state->hasShutdown());
    }

    private function construct(array $spanProcessors = []): TracerSharedState
    {
        $processor = match (count($spanProcessors)) {
            0 => new NoopSpanProcessor(),
            1 => $spanProcessors[0],
            default => new MultiSpanProcessor(...$spanProcessors),
        };

        return new TracerSharedState(
            $this->idGenerator,
            $this->resourceInfo,
            $this->spanLimits,
            $this->sampler,
            $processor,
        );
    }
}
