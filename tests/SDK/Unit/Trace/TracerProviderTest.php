<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Trace;

use OpenTelemetry\API\Trace as API;
use OpenTelemetry\SDK\Trace\NoopTracer;
use OpenTelemetry\SDK\Trace\TracerProvider;
use PHPUnit\Framework\TestCase;

class TracerProviderTest extends TestCase
{
    public function testReusesSameInstance(): void
    {
        $provider = new TracerProvider();

        $t1 = $provider->getTracer('foo');
        $t2 = $provider->getTracer('foo');
        $t3 = $provider->getTracer('foo', '2.0.0');

        $this->assertSame($t1, $t2);
        $this->assertNotSame($t1, $t3);
        $this->assertNotSame($t2, $t3);
    }

    /**
     * @runInSeparateProcess
     */
    public function test_tracerProvider_returnsNoopTracerIfNoDefaultIsSet(): void
    {
        $this->assertInstanceOf(NoopTracer::class, TracerProvider::getDefaultTracer());
    }

    /**
     * @runInSeparateProcess
     */
    public function test_tracerProvider_acceptsDefaultTracer(): void
    {
        $tracer = $this->getMockBuilder(API\TracerInterface::class)->getMock();
        TracerProvider::setDefaultTracer($tracer);
        $this->assertSame($tracer, TracerProvider::getDefaultTracer());
    }
}
