<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Export\Stream;

use OpenTelemetry\SDK\Common\Export\Stream\StreamTransport;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(StreamTransport::class)]
class StreamTransportTest extends TestCase
{
    public function test_content_type(): void
    {
        $stream = fopen('php://memory', 'a+b');
        $transport = new StreamTransport($stream, 'application/json');
        $this->assertSame('application/json', $transport->contentType());
    }

    public function test_send_writes_payload(): void
    {
        $stream = fopen('php://memory', 'a+b');
        $transport = new StreamTransport($stream, 'application/json');
        $future = $transport->send('test payload');
        $this->assertNull($future->await());
        fseek($stream, 0);
        $this->assertSame('test payload', stream_get_contents($stream));
    }

    public function test_send_after_shutdown_returns_error(): void
    {
        $stream = fopen('php://memory', 'a+b');
        $transport = new StreamTransport($stream, 'application/json');
        $transport->shutdown();
        $future = $transport->send('test');
        $this->expectException(\BadMethodCallException::class);
        $future->await();
    }

    public function test_shutdown_returns_true(): void
    {
        $stream = fopen('php://memory', 'a+b');
        $transport = new StreamTransport($stream, 'application/json');
        $this->assertTrue($transport->shutdown());
    }

    public function test_shutdown_twice_returns_false(): void
    {
        $stream = fopen('php://memory', 'a+b');
        $transport = new StreamTransport($stream, 'application/json');
        $transport->shutdown();
        $this->assertFalse($transport->shutdown());
    }

    public function test_force_flush_returns_true(): void
    {
        $stream = fopen('php://memory', 'a+b');
        $transport = new StreamTransport($stream, 'application/json');
        $this->assertTrue($transport->forceFlush());
    }

    public function test_force_flush_after_shutdown_returns_false(): void
    {
        $stream = fopen('php://memory', 'a+b');
        $transport = new StreamTransport($stream, 'application/json');
        $transport->shutdown();
        $this->assertFalse($transport->forceFlush());
    }
}
