<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Extension\Propagator\B3;

use OpenTelemetry\Extension\Propagator\B3\B3DebugFlagContextKey;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\OpenTelemetry\Extension\Propagator\B3\B3DebugFlagContextKey::class)]
class B3DebugFlagContextKeyTest extends TestCase
{
    public function test_b3_debug_flag_key(): void
    {
        $this->assertSame(
            B3DebugFlagContextKey::instance(),
            B3DebugFlagContextKey::instance()
        );
    }
}
