<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace;

use OpenTelemetry\API\Trace as API;
use OpenTelemetry\API\Trace\NoopTracer;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use OpenTelemetry\SDK\Trace\TracerProvider;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \OpenTelemetry\SDK\Trace\TracerProvider
 */
class TracerProviderTest extends TestCase
{
    /**
     * @covers ::getTracer
     * @covers ::__construct
     */
    public function test_reuses_same_instance(): void
    {
        $provider = new TracerProvider(null);

        $t1 = $provider->getTracer('foo');
        $t2 = $provider->getTracer('foo');
        $t3 = $provider->getTracer('bar');

        $this->assertSame($t1, $t2);
        $this->assertNotSame($t1, $t3);
        $this->assertNotSame($t2, $t3);
    }

    /**
     * @covers ::getTracer
     * @covers ::__construct
     */
    public function test_reuses_same_instance_with_version(): void
    {
        $provider = new TracerProvider(null);

        $t1 = $provider->getTracer('foo', '1.0.0');
        $t2 = $provider->getTracer('foo', '1.0.0');
        $t3 = $provider->getTracer('foo', '2.0.0');

        $this->assertSame($t1, $t2);
        $this->assertNotSame($t1, $t3);
        $this->assertNotSame($t2, $t3);
    }

    /**
     * @covers ::getTracer
     * @covers ::__construct
     */
    public function test_reuses_same_instance_with_schema(): void
    {
        $provider = new TracerProvider(null);

        $t1 = $provider->getTracer('foo', '2.0.0', 'http://url');
        $t2 = $provider->getTracer('foo', '2.0.0', 'http://url');
        $t3 = $provider->getTracer('foo', '2.0.0', 'http://schemaurl');

        $this->assertSame($t1, $t2);
        $this->assertNotSame($t1, $t3);
        $this->assertNotSame($t2, $t3);
    }

    /**
     * @covers ::getDefaultTracer
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_tracer_provider_returns_noop_tracer_if_no_default_is_set(): void
    {
        $this->assertInstanceOf(NoopTracer::class, TracerProvider::getDefaultTracer());
    }

    /**
     * @covers ::setDefaultTracer
     * @covers ::getDefaultTracer
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_tracer_provider_accepts_default_tracer(): void
    {
        $tracer = $this->getMockBuilder(API\TracerInterface::class)->getMock();
        TracerProvider::setDefaultTracer($tracer);
        $this->assertSame($tracer, TracerProvider::getDefaultTracer());
    }

    /**
     * @covers ::getTracer
     */
    public function test_get_tracer_with_default_name(): void
    {
        $provider = new TracerProvider(null);

        $t1 = $provider->getTracer();
        $t2 = $provider->getTracer();

        $this->assertSame($t1, $t2);
    }

    /**
     * @covers ::shutdown
     */
    public function test_shutdown(): void
    {
        $provider = new TracerProvider(null);

        $this->assertTrue($provider->shutdown());
        // test additional shutdown
        $this->assertTrue($provider->shutdown());
    }

    /**
     * @covers ::forceFlush
     */
    public function test_force_flush(): void
    {
        $provider = new TracerProvider([]);

        $this->assertTrue($provider->forceFlush());
        // test additional forceFlush
        $this->assertTrue($provider->forceFlush());
    }

    /**
     * @covers ::getSampler
     */
    public function test_get_sampler(): void
    {
        $sampler = $this->createMock(SamplerInterface::class);
        $provider = new TracerProvider([], $sampler);

        $this->assertSame(
            $sampler,
            $provider->getSampler()
        );
    }

    /**
     * @covers ::getTracer
     */
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
