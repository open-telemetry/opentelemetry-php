<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace;

use OpenTelemetry\API\Trace\NoopTracer;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use OpenTelemetry\SDK\Trace\TracerProvider;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\OpenTelemetry\SDK\Trace\TracerProvider::class)]
class TracerProviderTest extends TestCase
{
    public function test_equal_for_same_name_without_version(): void
    {
        $provider = new TracerProvider(null);

        $t1 = $provider->getTracer('foo');
        $t2 = $provider->getTracer('foo');
        $t3 = $provider->getTracer('bar');

        $this->assertEquals($t1, $t2);
        $this->assertNotEquals($t1, $t3);
    }

    public function test_equal_for_same_name_with_version(): void
    {
        $provider = new TracerProvider(null);

        $t1 = $provider->getTracer('foo', '1.0.0');
        $t2 = $provider->getTracer('foo', '1.0.0');
        $t3 = $provider->getTracer('foo', '2.0.0');

        $this->assertEquals($t1, $t2);
        $this->assertNotEquals($t1, $t3);
    }

    #[\PHPUnit\Framework\Attributes\Group('trace-compliance')]
    public function test_equal_for_same_name_with_schema_and_version(): void
    {
        $provider = new TracerProvider(null);

        $t1 = $provider->getTracer('foo', '2.0.0', 'http://url');
        $t2 = $provider->getTracer('foo', '2.0.0', 'http://url');
        $t3 = $provider->getTracer('foo', '2.0.0', 'http://schemaurl');

        $this->assertEquals($t1, $t2);
        $this->assertNotEquals($t1, $t3);
    }

    #[\PHPUnit\Framework\Attributes\Group('trace-compliance')]
    public function test_shutdown(): void
    {
        $provider = new TracerProvider(null);

        $this->assertTrue($provider->shutdown());
        // test additional shutdown
        $this->assertTrue($provider->shutdown());
    }

    #[\PHPUnit\Framework\Attributes\Group('trace-compliance')]
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

    #[\PHPUnit\Framework\Attributes\Group('trace-compliance')]
    public function test_get_tracer_returns_noop_tracer_after_shutdown(): void
    {
        $provider = new TracerProvider([]);
        $provider->shutdown();

        $this->assertInstanceOf(
            NoopTracer::class,
            $provider->getTracer('foo')
        );
    }
}
