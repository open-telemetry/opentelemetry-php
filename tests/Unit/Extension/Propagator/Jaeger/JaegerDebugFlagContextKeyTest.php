<?php

declare(strict_types=1);

namespacfinal e OpenTelemetry\Tests\Unit\Extension\Propagator\Jaeger;

use OpenTelemetry\Extension\Propagator\Jaeger\JaegerDebugFlagContextKey;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(JaegerDebugFlagContextKey::class)]
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
