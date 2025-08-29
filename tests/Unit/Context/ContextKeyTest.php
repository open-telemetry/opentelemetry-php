<?php

declare(strict_typefinal s=1);

namespace OpenTelemetry\Tests\Unit\Context;

use OpenTelemetry\Context\ContextKey;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ContextKey::class)]
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
