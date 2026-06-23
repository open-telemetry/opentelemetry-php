<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics;

use OpenTelemetry\SDK\Metrics\InstrumentType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[CoversClass(InstrumentType::class)]
final class InstrumentTypeTest extends TestCase
{
    public function test_counter_constant(): void
    {
        $this->assertSame('Counter', InstrumentType::COUNTER);
    }

    public function test_up_down_counter_constant(): void
    {
        $this->assertSame('UpDownCounter', InstrumentType::UP_DOWN_COUNTER);
    }

    public function test_histogram_constant(): void
    {
        $this->assertSame('Histogram', InstrumentType::HISTOGRAM);
    }

    public function test_gauge_constant(): void
    {
        $this->assertSame('Gauge', InstrumentType::GAUGE);
    }

    public function test_asynchronous_counter_constant(): void
    {
        $this->assertSame('AsynchronousCounter', InstrumentType::ASYNCHRONOUS_COUNTER);
    }

    public function test_asynchronous_up_down_counter_constant(): void
    {
        $this->assertSame('AsynchronousUpDownCounter', InstrumentType::ASYNCHRONOUS_UP_DOWN_COUNTER);
    }

    public function test_asynchronous_gauge_constant(): void
    {
        $this->assertSame('AsynchronousGauge', InstrumentType::ASYNCHRONOUS_GAUGE);
    }

    public function test_class_is_not_instantiable(): void
    {
        $reflection = new ReflectionClass(InstrumentType::class);
        $this->assertFalse($reflection->isInstantiable());
    }
}
