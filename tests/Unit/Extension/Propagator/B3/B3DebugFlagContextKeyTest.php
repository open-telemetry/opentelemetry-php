<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Extension\Propagator\B3;

use OpenTelemetry\Context\ContextKey;
use OpenTelemetry\Extension\Propagator\B3\B3DebugFlagContextKey;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\Extension\Propagator\B3\B3DebugFlagContextKey
 */
class B3DebugFlagContextKeyTest extends TestCase
{
    public function test_b3_debug_flag_key(): void
    {
        $this->assertEquals(
            new ContextKey('OpenTelemetry Context Key B3 Debug Flag'),
            B3DebugFlagContextKey::instance()
        );
    }
}
