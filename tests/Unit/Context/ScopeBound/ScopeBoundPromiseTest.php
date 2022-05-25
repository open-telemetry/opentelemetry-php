<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Context\ScopeBound;

use InvalidArgumentException;
use OpenTelemetry\Context\ContextKey;
use OpenTelemetry\Context\ContextStorage;
use OpenTelemetry\Context\ScopeBound\ScopeBoundPromise;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \OpenTelemetry\Context\ScopeBound\ScopeBoundPromise
 * @covers \OpenTelemetry\Context\ScopeBound\ContextHolder
 */
class ScopeBoundPromiseTest extends TestCase
{
    public function test_promise_then_propagates_value_in_promise()
    {
        $storage = new ContextStorage();

        $promise = self::getFulfilledPromise(5);

        ScopeBoundPromise::wrap($promise, $storage)
            ->then(fn ($value) => $value)
            ->then(fn ($value) => $this->assertSame(5, $value))
        ;
    }

    public function test_promise_then_propagates_context_in_promise()
    {
        $storage = new ContextStorage();

        $contextKey = new ContextKey();
        $promise = self::getFulfilledPromise();

        ScopeBoundPromise::wrap($promise, $storage)
            ->then(fn () => $storage->attach($storage->current()->with($contextKey, 'value')))
            ->then(fn () => $this->assertSame('value', $storage->current()->get($contextKey)))
        ;
    }

    public function test_promise_then_preserves_scope_outside_of_promise()
    {
        $storage = new ContextStorage();

        $contextKey = new ContextKey();
        $promise = self::getFulfilledPromise();

        ScopeBoundPromise::wrap($promise, $storage)
            ->then(fn () => $storage->attach($storage->current()->with($contextKey, 'value')))
        ;

        $this->assertNull($storage->current()->get($contextKey));
    }

    public function test_promise_invalid()
    {
        $this->expectException(InvalidArgumentException::class);

        ScopeBoundPromise::wrap(new stdClass());
    }

    private static function getFulfilledPromise($value = null): object
    {
        return new class($value) {
            private $value;

            /**
             * @param mixed $value
             */
            public function __construct($value)
            {
                $this->value = $value;
            }

            public function then(?callable $onFulfilled = null): self
            {
                return $onFulfilled
                    ? new self($onFulfilled($this->value))
                    : $this;
            }
        };
    }
}
