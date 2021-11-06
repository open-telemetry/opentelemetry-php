<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Trace\SpanProcessor;

use Monolog\Test\TestCase;
use OpenTelemetry\SDK\Trace\ReadableSpanInterface;
use OpenTelemetry\SDK\Trace\ReadWriteSpanInterface;
use OpenTelemetry\SDK\Trace\SpanProcessor\MultiSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;
use PHPUnit\Framework\MockObject\MockObject;

class MultiSpanProcessorTest extends TestCase
{
    private array $spanProcessors = [];

    public function testGetSpanProcessors(): void
    {
        $this->assertEquals(
            $this->getSpanProcessors(),
            $this->createMultiSpanProcessor()->getSpanProcessors()
        );
    }

    public function testAddSpanProcessor(): void
    {
        $multiProcessor = $this->createMultiSpanProcessor();
        $processor = $this->createMock(SpanProcessorInterface::class);
        $multiProcessor->addSpanProcessor($processor);
        $processors = array_merge(
            [$this->createMock(SpanProcessorInterface::class)],
            $this->getSpanProcessors()
        );

        $this->assertEquals(
            $processors,
            $multiProcessor->getSpanProcessors()
        );
    }

    public function testOnStart(): void
    {
        /** @var MockObject $processor */
        foreach ($this->getSpanProcessors() as $processor) {
            $processor->expects($this->once())
                ->method('onStart');
        }

        $this->createMultiSpanProcessor()
            ->onStart(
                $this->createMock(ReadWriteSpanInterface::class)
            );
    }

    public function testOnEnd(): void
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

    public function testShutdown(): void
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

    public function testShutdownOneFailed(): void
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

    public function testForceFlush(): void
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

    public function testForceFlushOneFailed(): void
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
        return !empty($this->spanProcessors)
            ? $this->spanProcessors
            : $this->spanProcessors = [
                $this->createMock(SpanProcessorInterface::class),
                $this->createMock(SpanProcessorInterface::class),
            ];
    }
}
