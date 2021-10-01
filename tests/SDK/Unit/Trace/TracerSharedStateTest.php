<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Trace;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Trace\IdGenerator;
use OpenTelemetry\SDK\Trace\Sampler;
use OpenTelemetry\SDK\Trace\SpanLimitsBuilder;
use OpenTelemetry\SDK\Trace\SpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessor\NoopSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerSharedState;

class TracerSharedStateTest extends MockeryTestCase
{
    /** @var MockInterface&IdGenerator */
    private $idGenerator;

    /** @var MockInterface&ResourceInfo */
    private $resourceInfo;

    /** @var MockInterface&Sampler */
    private $sampler;

    protected function setUp(): void
    {
        $this->idGenerator = Mockery::mock(IdGenerator::class);
        $this->resourceInfo = Mockery::mock(ResourceInfo::class);
        $this->sampler = Mockery::mock(Sampler::class);
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

    public function test_construct_noSpanProcessors(): void
    {
        $this->assertInstanceOf(
            SpanProcessor\NoopSpanProcessor::class,
            $this->construct()->getSpanProcessor()
        );
    }

    public function test_construct_oneSpanProcessor(): void
    {
        $processor = Mockery::mock(SpanProcessor::class);

        $this->assertSame(
            $processor,
            $this->construct([$processor])->getSpanProcessor()
        );
    }

    public function test_construct_multipleSpanProcessors(): void
    {
        $processor1 = Mockery::mock(SpanProcessor::class);
        $processor2 = Mockery::mock(SpanProcessor::class);

        $this->assertInstanceOf(
            SpanProcessor\SpanMultiProcessor::class,
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
