<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Context\Propagation;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\Propagation\NullPropagator;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\Context\Propagation\NullPropagator
 */
class NullPropagatorTest extends TestCase
{
    public function test_fields(): void
    {
        $this->assertSame(
            [],
            (new NullPropagator())->fields()
        );
    }

    public function test_inject_extract(): void
    {
        $propagator = new NullPropagator();
        $carrier = [];

        $propagator->inject($carrier);

        $context = Context::getCurrent();

        $this->assertSame(
            $context,
            $propagator->extract($carrier)
        );
    }
}
