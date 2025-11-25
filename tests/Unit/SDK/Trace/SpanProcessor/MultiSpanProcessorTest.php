<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace\SpanProcessor;

use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Trace\ExtendedSpanProcessorInterface;
use OpenTelemetry\SDK\Trace\ReadableSpanInterface;
use OpenTelemetry\SDK\Trace\ReadWriteSpanInterface;
use OpenTelemetry\SDK\Trace\SpanProcessor\MultiSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(MultiSpanProcessor::class)]
class MultiSpanProcessorTest extends TestCase
{
    private array $spanProcessors = [];

    public function test_get_span_processors(): void
    {
        $this->assertEquals(
            $this->getSpanProcessors(),
            $this->createMultiSpanProcessor()->getSpanProcessors()
        );
    }

    public function test_add_span_processor(): void
    {
        $multiProcessor = $this->createMultiSpanProcessor();
        $processor = $this->createMock(SpanProcessorInterface::class);
        $multiProcessor->addSpanProcessor($processor);
        $extendedProcessor = $this->createMock(ExtendedSpanProcessorInterface::class);
        $multiProcessor->addSpanProcessor($extendedProcessor);
        $processors = array_merge(
            $this->getSpanProcessors(),
            [$this->createMock(SpanProcessorInterface::class)],
            [$this->createMock(ExtendedSpanProcessorInterface::class)],
        );

        $this->assertEquals(
            $processors,
            $multiProcessor->getSpanProcessors()
        );
    }

    public function test_on_start(): void
    {
        /** @var MockObject $processor */
        foreach ($this->getSpanProcessors() as $processor) {
            $processor->expects($this->once())
                ->method('onStart');
        }

        $this->createMultiSpanProcessor()
            ->onStart(
                $this->createMock(ReadWriteSpanInterface::class),
                Context::getCurrent(),
            );
    }

    public function test_on_ending(): void
    {
        /** @var MockObject $processor */
        foreach ($this->getSpanProcessors() as $processor) {
            if ($processor instanceof ExtendedSpanProcessorInterface) {
                $processor->expects($this->once())
                    ->method('onEnding');
            }
        }

        $this->createMultiSpanProcessor()
            ->onEnding(
                $this->createMock(ReadWriteSpanInterface::class)
            );
    }

    public function test_on_end(): void
    {
        /** @var MockObject $processor */
        foreach ($this->getSpanProcessors() as $processor) {
            $processor->expects($this->once())
                ->method('onEnd');
        }

        $this->createMultiSpanProcessor()
            ->onEnd(
                $this->createMock(ReadableSpanInterface::class)
            );
    }

    public function test_shutdown(): void
    {
        /** @var MockObject $processor */
        foreach ($this->getSpanProcessors() as $processor) {
            $processor->expects($this->once())
                ->method('shutdown')
                ->willReturn(true);
        }

        $this->assertTrue(
            $this->createMultiSpanProcessor()
                ->shutdown()
        );
    }

    public function test_shutdown_one_failed(): void
    {
        /** @var MockObject $processor */
        foreach ($this->getSpanProcessors() as $i => $processor) {
            $processor->expects($this->once())
                ->method('shutdown')
                ->willReturn(!(bool) $i);
        }

        $this->assertFalse(
            $this->createMultiSpanProcessor()
                ->shutdown()
        );
    }

    public function test_force_flush(): void
    {
        /** @var MockObject $processor */
        foreach ($this->getSpanProcessors() as $processor) {
            $processor->expects($this->once())
                ->method('forceFlush')
                ->willReturn(true);
        }

        $this->assertTrue(
            $this->createMultiSpanProcessor()
                ->forceFlush()
        );
    }

    public function test_force_flush_one_failed(): void
    {
        /** @var MockObject $processor */
        foreach ($this->getSpanProcessors() as $i => $processor) {
            $processor->expects($this->once())
                ->method('forceFlush')
                ->willReturn(!(bool) $i);
        }

        $this->assertFalse(
            $this->createMultiSpanProcessor()
                ->forceFlush()
        );
    }

    private function createMultiSpanProcessor(): MultiSpanProcessor
    {
        return new MultiSpanProcessor(
            $this->getSpanProcessors()[0],
            $this->getSpanProcessors()[1]
        );
    }

    private function getSpanProcessors(): array
    {
        return $this->spanProcessors === []
            ? $this->spanProcessors = [
                $this->createMock(SpanProcessorInterface::class),
                $this->createMock(ExtendedSpanProcessorInterface::class),
            ]
            : $this->spanProcessors;
    }
}
