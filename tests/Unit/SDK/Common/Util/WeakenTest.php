<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Util;

use Closure;
use function OpenTelemetry\SDK\Common\Util\closure;
use function OpenTelemetry\SDK\Common\Util\weaken;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use WeakReference;

/**
 * @todo configure phpunit coverage with attributes
 */
#[CoversNothing]
final class WeakenTest extends TestCase
{
    public function test_weakened_closure_calls_original_closure(): void
    {
        $object = new class() {
            public function foo(): int
            {
                return 5;
            }
        };

        $weakened = weaken(closure($object->foo(...)));

        $this->assertSame(5, $weakened());
    }

    public function test_weakened_closure_weakens_bound_this(): void
    {
        $object = new class() {
            public function foo(): int
            {
                return 5;
            }
        };

        $weakened = weaken(closure($object->foo(...)));
        $reference = WeakReference::create($object);

        $object = null;

        $this->assertNull($reference->get());
        $this->assertNull($weakened());
    }

    public function test_weaken_assigns_bound_this_to_target(): void
    {
        $object = new class() {
            public function foo(): int
            {
                return 5;
            }
        };

        weaken(closure($object->foo(...)), $target);

        $this->assertSame($object, $target);
    }

    public function test_weaken_is_noop_if_no_bound_this(): void
    {
        $closure = static fn (): int => 5;

        $this->assertSame($closure, weaken($closure));
    }

    public function test_weaken_preserves_scope(): void
    {
        $b = new B();
        $this->assertSame(5, weaken($b->closure())());
    }
}

class A
{
    public function closure(): \Closure
    {
        return Closure::fromCallable($this->private(...));
    }
    private function private(): int
    {
        return 5;
    }
}
class B extends A
{
}
