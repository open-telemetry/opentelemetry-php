<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Contrib\Propagator;

use OpenTelemetry\Context\ContextKey;
use OpenTelemetry\Contrib\Propagator\B3DebugFlagContextKey;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\Contrib\Propagator\B3DebugFlagContextKey
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
