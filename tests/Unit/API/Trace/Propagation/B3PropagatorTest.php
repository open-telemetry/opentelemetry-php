<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\API\Unit\Trace\Propagation;

use OpenTelemetry\API\Trace\Propagation\B3Propagator;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\API\Trace\Propagation\B3Propagator
 */
class B3PropagatorTest extends TestCase
{
    public function test_b3multi_fields(): void
    {
        $propagator = B3Propagator::getB3MultiHeaderInstance();
        $this->assertSame(
            ['X-B3-TraceId', 'X-B3-SpanId', 'X-B3-ParentSpanId', 'X-B3-Sampled', 'X-B3-Flags'],
            $propagator->fields()
        );
    }

    public function test_b3single_fields(): void
    {
        $propagator = B3Propagator::getB3SingleHeaderInstance();
        $this->assertSame(
            ['b3'],
            $propagator->fields()
        );
    }
}
