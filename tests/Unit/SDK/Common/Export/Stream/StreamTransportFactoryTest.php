<?php

declare(strict_types=1);

namespace OpenTelemetry\Example\Unit\SDK\Common\Export\Stream;

use OpenTelemetry\SDK\Common\Export\Stream\StreamTransportFactory;
use PHPUnit\Framework\TestCase;
use ReflectionObject;

/**
 * @covers \OpenTelemetry\SDK\Common\Export\Stream\StreamTransportFactory
 */
class StreamTransportFactoryTest extends TestCase
{
    public function test_creates_stream(): void
    {
        $factory = new StreamTransportFactory();
        $transport = $factory->create('php://stdout', 'a');
        $this->expectOutputString('payload');
        $transport->send('payload')->await();
    }
}
