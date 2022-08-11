<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics\Stream;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextStorageInterface;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Metrics\Stream\MultiStreamWriter;
use OpenTelemetry\SDK\Metrics\Stream\StreamWriter;
use OpenTelemetry\SDK\Metrics\Stream\WritableMetricStreamInterface;
use PHPUnit\Framework\TestCase;

final class StreamWriterTest extends TestCase
{
    /**
     * @covers \OpenTelemetry\SDK\Metrics\Stream\StreamWriter
     */
    public function test_stream_writer(): void
    {
        $stream = $this->createMock(WritableMetricStreamInterface::class);
        $stream->expects($this->once())->method('record')->with(5, Attributes::create(['foo' => 1]), $this->anything(), 3);

        $w = new StreamWriter(null, Attributes::factory(), $stream);
        $w->record(5, ['foo' => 1], null, 3);
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\Stream\MultiStreamWriter
     */
    public function test_multi_stream_writer(): void
    {
        $streams = [];
        for ($i = 0; $i < 3; $i++) {
            $stream = $this->createMock(WritableMetricStreamInterface::class);
            $stream->expects($this->once())->method('record')->with(5, Attributes::create(['foo' => 1]), $this->anything(), 3);

            $streams[] = $stream;
        }

        $w = new MultiStreamWriter(null, Attributes::factory(), $streams);
        $w->record(5, ['foo' => 1], null, 3);
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\Stream\StreamWriter
     */
    public function test_stream_writer_provides_current_context_on_context_null(): void
    {
        $context = Context::getRoot()->with(Context::createKey('-'), 5);
        $contextStorage = $this->createMock(ContextStorageInterface::class);
        $contextStorage->method('current')->willReturn($context);

        $stream = $this->createMock(WritableMetricStreamInterface::class);
        $stream->expects($this->once())->method('record')->with(5, Attributes::create(['foo' => 1]), $context, 3);

        $w = new StreamWriter($contextStorage, Attributes::factory(), $stream);
        $w->record(5, ['foo' => 1], null, 3);
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\Stream\StreamWriter
     */
    public function test_stream_writer_context_provides_context_on_context_provided(): void
    {
        $context = Context::getRoot()->with(Context::createKey('-'), 5);
        $contextStorage = $this->createMock(ContextStorageInterface::class);

        $stream = $this->createMock(WritableMetricStreamInterface::class);
        $stream->expects($this->once())->method('record')->with(5, Attributes::create(['foo' => 1]), $context, 3);

        $w = new StreamWriter($contextStorage, Attributes::factory(), $stream);
        $w->record(5, ['foo' => 1], $context, 3);
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\Stream\StreamWriter
     */
    public function test_stream_writer_context_provides_root_context_on_context_false(): void
    {
        $contextStorage = $this->createMock(ContextStorageInterface::class);

        $stream = $this->createMock(WritableMetricStreamInterface::class);
        $stream->expects($this->once())->method('record')->with(5, Attributes::create(['foo' => 1]), Context::getRoot(), 3);

        $w = new StreamWriter($contextStorage, Attributes::factory(), $stream);
        $w->record(5, ['foo' => 1], false, 3);
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\Stream\MultiStreamWriter
     */
    public function test_multi_stream_writer_provides_current_context_on_context_null(): void
    {
        $context = Context::getRoot()->with(Context::createKey('-'), 5);
        $contextStorage = $this->createMock(ContextStorageInterface::class);
        $contextStorage->method('current')->willReturn($context);

        $stream = $this->createMock(WritableMetricStreamInterface::class);
        $stream->expects($this->once())->method('record')->with(5, Attributes::create(['foo' => 1]), $context, 3);

        $w = new MultiStreamWriter($contextStorage, Attributes::factory(), [$stream]);
        $w->record(5, ['foo' => 1], null, 3);
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\Stream\MultiStreamWriter
     */
    public function test_multi_stream_writer_context_provides_context_on_context_provided(): void
    {
        $context = Context::getRoot()->with(Context::createKey('-'), 5);
        $contextStorage = $this->createMock(ContextStorageInterface::class);

        $stream = $this->createMock(WritableMetricStreamInterface::class);
        $stream->expects($this->once())->method('record')->with(5, Attributes::create(['foo' => 1]), $context, 3);

        $w = new MultiStreamWriter($contextStorage, Attributes::factory(), [$stream]);
        $w->record(5, ['foo' => 1], $context, 3);
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\Stream\MultiStreamWriter
     */
    public function test_multi_stream_writer_context_provides_root_context_on_context_false(): void
    {
        $contextStorage = $this->createMock(ContextStorageInterface::class);

        $stream = $this->createMock(WritableMetricStreamInterface::class);
        $stream->expects($this->once())->method('record')->with(5, Attributes::create(['foo' => 1]), Context::getRoot(), 3);

        $w = new MultiStreamWriter($contextStorage, Attributes::factory(), [$stream]);
        $w->record(5, ['foo' => 1], false, 3);
    }
}
