<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace\SpanProcessor;

use LogicException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use OpenTelemetry\API\Behavior\Internal\Logging;
use OpenTelemetry\API\Behavior\Internal\LogWriter\LogWriterInterface;
use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\SpanContextInterface;
use OpenTelemetry\API\Trace\SpanContextValidator;
use OpenTelemetry\API\Trace\TraceFlags;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Future\CompletedFuture;
use OpenTelemetry\SDK\Trace\ReadableSpanInterface;
use OpenTelemetry\SDK\Trace\ReadWriteSpanInterface;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\Tests\Unit\SDK\Util\SpanData;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LogLevel;

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
    /** @var LogWriterInterface&MockObject $logWriter */
    private LogWriterInterface $logWriter;

    private SpanContextInterface $sampledSpanContext;
    private SpanContextInterface $nonSampledSpanContext;

    protected function setUp(): void
    {
        $this->logWriter = $this->createMock(LogWriterInterface::class);
        Logging::setLogWriter($this->logWriter);
        $this->readWriteSpan = Mockery::mock(ReadWriteSpanInterface::class);
        $this->readableSpan = Mockery::mock(ReadableSpanInterface::class);

        $this->sampledSpanContext = SpanContext::create(
            SpanContextValidator::INVALID_TRACE,
            SpanContextValidator::INVALID_SPAN,
            TraceFlags::SAMPLED
        );

        $this->nonSampledSpanContext = SpanContext::getInvalid();

        $this->spanExporter = Mockery::mock(SpanExporterInterface::class);
        $this->simpleSpanProcessor = new SimpleSpanProcessor($this->spanExporter);
    }

    public function tearDown(): void
    {
        Logging::reset();
    }

    public function test_on_start(): void
    {
        $this->simpleSpanProcessor->onStart($this->readWriteSpan, Context::getRoot());
        $this->spanExporter->shouldNotReceive('export');
    }

    public function test_on_end_after_shutdown(): void
    {
        $this->spanExporter->shouldReceive('shutdown');
        $this->spanExporter->shouldNotReceive('export');
        $this->simpleSpanProcessor->shutdown();
        $this->simpleSpanProcessor->onEnd($this->readableSpan);
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

    /**
     * @psalm-suppress UndefinedVariable
     */
    public function test_does_not_trigger_concurrent_export(): void
    {
        $spanData = new SpanData();
        $count = 3;
        $this->readableSpan->expects('getContext')->times($count)->andReturn($this->sampledSpanContext);
        $this->readableSpan->expects('toSpanData')->times($count)->andReturn($spanData);

        $this->spanExporter->expects('export')->times($count)->andReturnUsing(function () use (&$running, &$count) {
            $this->assertNotTrue($running);
            $running = true;
            if (--$count) {
                $this->simpleSpanProcessor->onEnd($this->readableSpan);
            }
            $running = false;

            return 0;
        });

        $this->simpleSpanProcessor->onEnd($this->readableSpan);
    }

    // TODO: Add test to ensure exporter is retried on failure.

    public function test_force_flush(): void
    {
        $this->spanExporter->expects('forceFlush')->andReturn(true);
        $this->assertTrue($this->simpleSpanProcessor->forceFlush());
    }

    public function test_force_flush_after_shutdown(): void
    {
        $this->spanExporter->expects('shutdown')->andReturn(true);
        $this->spanExporter->shouldNotReceive('forceFlush');
        $this->simpleSpanProcessor->shutdown();
        $this->simpleSpanProcessor->forceFlush();
    }

    public function test_shutdown(): void
    {
        $this->spanExporter->expects('shutdown')->andReturnTrue();

        $this->assertTrue($this->simpleSpanProcessor->shutdown());
        $this->assertFalse($this->simpleSpanProcessor->shutdown());
    }

    public function test_throwing_exporter_export(): void
    {
        $exporter = $this->createMock(SpanExporterInterface::class);
        $exporter->method('forceFlush')->willReturn(true);
        $exporter->method('export')->willThrowException(new LogicException());

        $this->logWriter->expects($this->once())->method('write')->with(LogLevel::ERROR);

        $processor = new SimpleSpanProcessor($exporter);

        $this->readableSpan->expects('getContext')->andReturn($this->sampledSpanContext);
        $this->readableSpan->expects('toSpanData')->andReturn(new SpanData());

        $processor->onStart($this->readWriteSpan, Context::getCurrent());
        $processor->onEnd($this->readableSpan);
    }

    public function test_throwing_exporter_flush(): void
    {
        $exporter = $this->createMock(SpanExporterInterface::class);
        $exporter->method('forceFlush')->willThrowException(new LogicException());

        $this->expectException(LogicException::class);

        $processor = new SimpleSpanProcessor($exporter);

        $processor->forceFlush();
    }
}
