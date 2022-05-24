<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Context\ScopeBound;

use OpenTelemetry\Context\ContextKey;
use OpenTelemetry\Context\ContextStorage;
use OpenTelemetry\Context\ScopeBound\ScopeBoundCallable;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\Context\ScopeBound\ScopeBoundCallable
 */
class ScopeBoundCallableTest extends TestCase
{
    public function test_scope_bound_callable_preserves_original_scope(): void
    {
        $storage = new ContextStorage();

        $contextKey = new ContextKey();
        $scope = $storage->attach($storage->current()->with($contextKey, 'value'));

        $callable = fn (ContextKey $contextKey) => $storage->current()->get($contextKey);
        $scopeBoundCallable = ScopeBoundCallable::wrap($callable, $storage);

        $scope->detach();

        $this->assertNull($callable($contextKey));
        $this->assertSame('value', $scopeBoundCallable($contextKey));
        $this->assertNull($callable($contextKey));
    }
}
