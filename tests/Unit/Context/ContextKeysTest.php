<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Context;

use OpenTelemetry\Context\ContextKeys;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\OpenTelemetry\Context\ContextKeys::class)]
final class ContextKeysTest extends TestCase
{
    public function test_span_context_key(): void
    {
        $this->assertSame(ContextKeys::span(), ContextKeys::span());
    }

    public function test_baggage_context_key(): void
    {
        $this->assertSame(ContextKeys::baggage(), ContextKeys::baggage());
    }
}
