<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Util;

use Closure;
use function OpenTelemetry\SDK\Common\Util\closure;
use function OpenTelemetry\SDK\Common\Util\weaken;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\TestCase;

#[CoversFunction('\OpenTelemetry\SDK\Common\Util\closure')]
#[CoversFunction('\OpenTelemetry\SDK\Common\Util\weaken')]
final class FunctionsTest extends TestCase
{
    public function test_closure_converts_callable_to_closure(): void
    {
        $callable = 'strlen';
        $result = closure($callable);

        $this->assertInstanceOf(Closure::class, $result);
        $this->assertSame(5, $result('hello'));
    }

    public function test_closure_from_invokable_object(): void
    {
        $invokable = new class() {
            public function __invoke(int $x): int
            {
                return $x * 2;
            }
        };

        $result = closure($invokable);

        $this->assertInstanceOf(Closure::class, $result);
        $this->assertSame(10, $result(5));
    }

    public function test_closure_from_array_callable(): void
    {
        $object = new class() {
            public function add(int $a, int $b): int
            {
                return $a + $b;
            }
        };

        $result = closure([$object, 'add']);

        $this->assertInstanceOf(Closure::class, $result);
        $this->assertSame(7, $result(3, 4));
    }

    public function test_weaken_returns_closure_without_bound_this(): void
    {
        $fn = static fn (int $x): int => $x + 1;

        $weakened = weaken($fn);

        $this->assertSame(6, $weakened(5));
    }

    public function test_weaken_weakens_bound_object(): void
    {
        $object = new class() {
            public int $value = 42;

            public function getValue(): int
            {
                return $this->value;
            }
        };

        $target = null;
        $weakened = weaken(closure($object->getValue(...)), $target);

        $this->assertSame($object, $target);
        $this->assertSame(42, $weakened());
    }

    public function test_weaken_returns_null_after_target_is_collected(): void
    {
        $object = new class() {
            public function getValue(): int
            {
                return 42;
            }
        };

        $target = null;
        $weakened = weaken(closure($object->getValue(...)), $target);

        // Remove all strong references
        unset($object, $target);

        $this->assertNull($weakened());
    }
}
