<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Trace;

use OpenTelemetry\API\Trace as API;
use OpenTelemetry\API\Trace\NoopTracer;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use OpenTelemetry\SDK\Trace\TracerProvider;
use PHPUnit\Framework\TestCase;

class TracerProviderTest extends TestCase
{
    public function test_reuses_same_instance(): void
    {
        $provider = new TracerProvider(null);

        $t1 = $provider->getTracer('foo');
        $t2 = $provider->getTracer('foo');
        $t3 = $provider->getTracer('foo', '2.0.0');

        $this->assertSame($t1, $t2);
        $this->assertNotSame($t1, $t3);
        $this->assertNotSame($t2, $t3);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_tracer_provider_returns_noop_tracer_if_no_default_is_set(): void
    {
        $this->assertInstanceOf(NoopTracer::class, TracerProvider::getDefaultTracer());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_tracer_provider_accepts_default_tracer(): void
    {
        $tracer = $this->getMockBuilder(API\TracerInterface::class)->getMock();
        TracerProvider::setDefaultTracer($tracer);
        $this->assertSame($tracer, TracerProvider::getDefaultTracer());
    }

    public function test_get_tracer_with_default_name(): void
    {
        $provider = new TracerProvider(null);

        $t1 = $provider->getTracer();
        $t2 = $provider->getTracer();

        $this->assertSame($t1, $t2);
    }

    public function test_shut_down(): void
    {
        $provider = new TracerProvider(null);

        $this->assertTrue($provider->shutdown());
        // test additional shutdown
        $this->assertTrue($provider->shutdown());
    }

    public function test_force_flush(): void
    {
        $provider = new TracerProvider([]);

        $this->assertTrue($provider->forceFlush());
        // test additional forceFlush
        $this->assertTrue($provider->forceFlush());
    }

    public function test_get_sampler(): void
    {
        $sampler = $this->createMock(SamplerInterface::class);
        $provider = new TracerProvider([], $sampler);

        $this->assertSame(
            $sampler,
            $provider->getSampler()
        );
    }

    public function test_get_tracer_after_shutdown(): void
    {
        $provider = new TracerProvider([]);
        $provider->shutdown();

        $this->assertInstanceOf(
            NoopTracer::class,
            $provider->getTracer('foo')
        );
    }
}
