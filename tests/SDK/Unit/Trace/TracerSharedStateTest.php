<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Trace;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Trace\IdGeneratorInterface;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use OpenTelemetry\SDK\Trace\SpanLimitsBuilder;
use OpenTelemetry\SDK\Trace\SpanProcessor\MultiSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessor\NoopSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;
use OpenTelemetry\SDK\Trace\TracerSharedState;

class TracerSharedStateTest extends MockeryTestCase
{
    /** @var MockInterface&IdGeneratorInterface */
    private $idGenerator;

    /** @var MockInterface&ResourceInfo */
    private $resourceInfo;

    /** @var MockInterface&SamplerInterface */
    private $sampler;

    protected function setUp(): void
    {
        $this->idGenerator = Mockery::mock(IdGeneratorInterface::class);
        $this->resourceInfo = Mockery::mock(ResourceInfo::class);
        $this->sampler = Mockery::mock(SamplerInterface::class);
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
        $processor = Mockery::mock(SpanProcessorInterface::class);

        $this->assertSame(
            $processor,
            $this->construct([$processor])->getSpanProcessor()
        );
    }

    public function test_construct_multiple_span_processors(): void
    {
        $processor1 = Mockery::mock(SpanProcessorInterface::class);
        $processor2 = Mockery::mock(SpanProcessorInterface::class);

        $this->assertInstanceOf(
            MultiSpanProcessor::class,
            $this->construct([$processor1, $processor2])->getSpanProcessor()
        );
    }

    private function construct(array $spanProcessors = []): TracerSharedState
    {
        return new TracerSharedState(
            $this->idGenerator,
            $this->resourceInfo,
            (new SpanLimitsBuilder())->build(),
            $this->sampler,
            empty($spanProcessors) ? [new NoopSpanProcessor()] : $spanProcessors,
        );
    }
}
