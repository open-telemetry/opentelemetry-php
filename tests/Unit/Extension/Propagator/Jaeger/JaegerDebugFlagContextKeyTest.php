<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Extension\Propagator\Jaeger;

use OpenTelemetry\Extension\Propagator\Jaeger\JaegerDebugFlagContextKey;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\Extension\Propagator\Jaeger\JaegerDebugFlagContextKey
 */
class JaegerDebugFlagContextKeyTest extends TestCase
{
    public function test_jaeger_debug_flag_key(): void
    {
        $this->assertSame(
            JaegerDebugFlagContextKey::instance(),
            JaegerDebugFlagContextKey::instance()
        );
    }
}
