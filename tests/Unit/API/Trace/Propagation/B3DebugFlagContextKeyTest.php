<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\API\Unit\Trace\Propagation;

use OpenTelemetry\API\Trace\Propagation\B3DebugFlagContextKey;
use OpenTelemetry\Context\ContextKey;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\API\Trace\Propagation\B3DebugFlagContextKey
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
