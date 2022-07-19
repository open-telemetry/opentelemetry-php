<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\API\Unit\Trace\Propagation;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use OpenTelemetry\API\Trace\Propagation\B3Propagator;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\API\Trace\Propagation\B3Propagator
 */
class B3PropagatorTest extends TestCase
{
    use EnvironmentVariables;

    private B3Propagator $b3Propagator;

    public function tearDown(): void
    {
        $this->restoreEnvironmentVariables();
        unset($this->b3Propagator);
    }

    public function test_b3multi_fields(): void
    {
        $this->setEnvironmentVariable('OTEL_PROPAGATORS', 'b3Multi');
        $this->b3Propagator = B3Propagator::getInstance();
        $this->assertSame(
            ['X-B3-TraceId', 'X-B3-SpanId', 'X-B3-ParentSpanId', 'X-B3-Sampled', 'X-B3-Flags'],
            $this->b3Propagator->fields()
        );
    }

//    public function test_default_fields(): void
//    {
//        $this->b3Propagator = B3Propagator::getInstance();
//        $this->assertSame(
//            ['b3'],
//            $this->b3Propagator->fields()
//        );
//    }
//
//    public function test_b3single_fields(): void
//    {
//        $this->setEnvironmentVariable('OTEL_PROPAGATORS', 'b3');
//        $this->b3Propagator = B3Propagator::getInstance();
//        $this->assertSame(
//            ['b3'],
//            $this->b3Propagator->fields()
//        );
//    }
}
