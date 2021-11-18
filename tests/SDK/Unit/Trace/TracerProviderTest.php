<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Trace;

use OpenTelemetry\SDK\Trace\NoopTracer;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use OpenTelemetry\SDK\Trace\TracerProvider;
use PHPUnit\Framework\TestCase;

class TracerProviderTest extends TestCase
{
    public function testReusesSameInstance(): void
    {
        $provider = new TracerProvider(null);

        $t1 = $provider->getTracer('foo');
        $t2 = $provider->getTracer('foo');
        $t3 = $provider->getTracer('foo', '2.0.0');

        $this->assertSame($t1, $t2);
        $this->assertNotSame($t1, $t3);
        $this->assertNotSame($t2, $t3);
    }

    public function testGetTracerWithDefaultName(): void
    {
        $provider = new TracerProvider(null);

        $t1 = $provider->getTracer();
        $t2 = $provider->getTracer();

        $this->assertSame($t1, $t2);
    }

    public function testShutDown(): void
    {
        $provider = new TracerProvider(null);

        $this->assertTrue($provider->shutdown());
        // test additional shutdown
        $this->assertTrue($provider->shutdown());
    }

    public function testForceFlush(): void
    {
        $provider = new TracerProvider([]);

        $this->assertTrue($provider->forceFlush());
        // test additional forceFlush
        $this->assertTrue($provider->forceFlush());
    }

    public function testGetSampler(): void
    {
        $sampler = $this->createMock(SamplerInterface::class);
        $provider = new TracerProvider([], $sampler);

        $this->assertSame(
            $sampler,
            $provider->getSampler()
        );
    }

    public function testGetTracerAfterShutdown(): void
    {
        $provider = new TracerProvider([]);
        $provider->shutdown();

        $this->assertInstanceOf(
            NoopTracer::class,
            $provider->getTracer('foo')
        );
    }
}
