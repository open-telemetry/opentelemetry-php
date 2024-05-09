<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Context;

use OpenTelemetry\Context\ContextKey;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\OpenTelemetry\Context\ContextKey::class)]
class ContextKeyTest extends TestCase
{
    public function test_name(): void
    {
        $name = 'foo';

        $this->assertSame(
            $name,
            (new ContextKey($name))
                ->name()
        );
    }
}
