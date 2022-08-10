<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace\SpanProcessor;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\SpanContextInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Future\CompletedFuture;
use OpenTelemetry\SDK\Trace\ReadableSpanInterface;
use OpenTelemetry\SDK\Trace\ReadWriteSpanInterface;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\Tests\Unit\SDK\Util\SpanData;

/**
 * @covers \OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor
 */
class SimpleSpanProcessorTest extends MockeryTestCase
{
    private SimpleSpanProcessor $simpleSpanProcessor;

    /** @var MockInterface&SpanExporterInterface */
    private $spanExporter;

    /** @var MockInterface&ReadWriteSpanInterface */
    private $readWriteSpan;

    /** @var MockInterface&ReadableSpanInterface */
    private $readableSpan;

    private SpanContextInterface $sampledSpanContext;
    private SpanContextInterface $nonSampledSpanContext;

    protected function setUp(): void
    {
        $this->readWriteSpan = Mockery::mock(ReadWriteSpanInterface::class);
        $this->readableSpan = Mockery::mock(ReadableSpanInterface::class);

        $this->sampledSpanContext = SpanContext::create(
            SpanContext::INVALID_TRACE,
            SpanContext::INVALID_SPAN,
            SpanContextInterface::TRACE_FLAG_SAMPLED
        );

        $this->nonSampledSpanContext = SpanContext::getInvalid();

        $this->spanExporter = Mockery::mock(SpanExporterInterface::class);
        $this->simpleSpanProcessor = new SimpleSpanProcessor($this->spanExporter);
    }

    public function test_on_start(): void
    {
        $this->simpleSpanProcessor->onStart($this->readWriteSpan, Context::getRoot());
        $this->spanExporter->shouldNotReceive('export');
    }

    public function test_on_end_sampled_span(): void
    {
        $spanData = new SpanData();
        $this->readableSpan->expects('getContext')->andReturn($this->sampledSpanContext);
        $this->readableSpan->expects('toSpanData')->andReturn($spanData);
        $this->spanExporter->expects('export')->with([$spanData])->andReturn(new CompletedFuture(0));
        $this->simpleSpanProcessor->onEnd($this->readableSpan);
    }

    public function test_on_end_non_sampled_span(): void
    {
        $this->readableSpan->expects('getContext')->andReturn($this->nonSampledSpanContext);
        $this->spanExporter->shouldNotReceive('export');
        $this->readableSpan->shouldReceive('toSpanData');
        $this->simpleSpanProcessor->onEnd($this->readableSpan);
    }

    // TODO: Add test to ensure exporter is retried on failure.

    public function test_force_flush(): void
    {
        $this->assertTrue($this->simpleSpanProcessor->forceFlush());
    }

    public function test_shutdown(): void
    {
        $this->spanExporter->expects('shutdown')->andReturnTrue();

        $this->assertTrue($this->simpleSpanProcessor->shutdown());
        $this->assertTrue($this->simpleSpanProcessor->shutdown());
    }

    public function test_shutdown_with_no_exporter(): void
    {
        $processor = new SimpleSpanProcessor(null);
        $this->assertTrue($processor->shutdown());
    }
}
