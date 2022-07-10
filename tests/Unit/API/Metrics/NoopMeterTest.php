<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Metrics;

use OpenTelemetry\API\Metrics\MetricKind;
use OpenTelemetry\API\Metrics\NoopMeter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\API\Metrics\NoopMeter
 */
class NoopMeterTest extends TestCase
{
    private NoopMeter $instance;

    protected function setUp(): void
    {
        $this->instance = new NoopMeter();
    }

    public function test_name(): void
    {
        $this->assertSame(NoopMeter::NAME, $this->instance->getName());
    }

    public function test_version(): void
    {
        $this->assertSame(NoopMeter::VERSION, $this->instance->getVersion());
    }

    public function test_counter(): void
    {
        $name = 'foo';
        $description = 'bar';

        $counter = $this->instance->newCounter($name, $description);

        $this->assertSame($name, $counter->getName());
        $this->assertSame($description, $counter->getDescription());
        $this->assertSame(MetricKind::COUNTER, $counter->getType());
        $this->assertSame(
            0,
            $counter->add(1)
                ->increment()
                ->getValue()
        );
    }

    public function test_up_down_counter(): void
    {
        $name = 'foo';
        $description = 'bar';

        $counter = $this->instance->newUpDownCounter($name, $description);

        $this->assertSame(
            0,
            $counter->add(1)
        );
    }

    public function test_value_recorder(): void
    {
        $name = 'foo';
        $description = 'bar';

        $recorder = $this->instance->newValueRecorder($name, $description);
        $recorder->record(1);

        $this->assertSame($name, $recorder->getName());
        $this->assertSame($description, $recorder->getDescription());
        $this->assertSame(MetricKind::VALUE_RECORDER, $recorder->getType());
        $this->assertSame(0, $recorder->getCount());
        $this->assertSame(0.0, $recorder->getMax());
        $this->assertSame(0.0, $recorder->getMin());
        $this->assertSame(0.0, $recorder->getSum());
    }
}
