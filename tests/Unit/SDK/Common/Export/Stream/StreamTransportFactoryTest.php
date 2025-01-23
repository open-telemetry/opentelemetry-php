<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Export\Stream;

use OpenTelemetry\SDK\Common\Export\Stream\StreamTransportFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(StreamTransportFactory::class)]
class StreamTransportFactoryTest extends TestCase
{
    public function test_creates_stream(): void
    {
        $factory = new StreamTransportFactory();
        $transport = $factory->create('php://output', 'a');
        $this->expectOutputString('payload');
        $transport->send('payload')->await();
    }

    public function test_type(): void
    {
        $factory = new StreamTransportFactory();
        $this->assertSame('stream', $factory->type());
    }

    public function test_priority(): void
    {
        $factory = new StreamTransportFactory();
        $this->assertSame(0, $factory->priority());
    }
}
