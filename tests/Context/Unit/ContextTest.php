<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Context\Unit;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextKey;
use PHPUnit\Framework\TestCase;

class ContextTest extends TestCase
{
    public function testActivate(): void
    {
        $context = new Context();

        $this->assertNotSame($context, Context::getCurrent());
        $context->activate();
        $this->assertSame($context, Context::getCurrent());
    }

    /**
     * @test
     */
    public function ctxCanStoreValuesByKey(): void
    {
        $key1 = new ContextKey('key1');
        $key2 = new ContextKey('key2');

        $ctx = (new Context())->with($key1, 'val1')->with($key2, 'val2');

        $this->assertSame($ctx->get($key1), 'val1');
        $this->assertSame($ctx->get($key2), 'val2');
    }

    /**
     * @test
     */
    public function setDoesNotMutateTheOriginal(): void
    {
        $key1 = new ContextKey();
        $key2 = new ContextKey();

        $parent = (new Context())->with($key1, 'foo');
        $child = $parent->with($key2, 'bar');

        $this->assertSame($child->get($key1), 'foo');
        $this->assertSame($child->get($key2), 'bar');
        $this->assertSame($parent->get($key1), 'foo');

        $this->assertNull($parent->get($key2));
    }

    /**
     * @test
     */
    public function ctxKeyNamesAreNotIds(): void
    {
        $key_name = 'foo';

        $key1 = new ContextKey($key_name);
        $key2 = new ContextKey($key_name);

        $ctx = (new Context())->with($key1, 'val1')->with($key2, 'val2');

        $this->assertSame($ctx->get($key1), 'val1');
        $this->assertSame($ctx->get($key2), 'val2');
    }

    /**
     * @test
     */
    public function emptyCtxKeysAreValid(): void
    {
        $key1 = new ContextKey();
        $key2 = new ContextKey();

        $ctx = (new Context())->with($key1, 'val1')->with($key2, 'val2');

        $this->assertSame($ctx->get($key1), 'val1');
        $this->assertSame($ctx->get($key2), 'val2');
    }

    /**
     * @test
     */
    public function ctxCanStoreScalarArrayNullAndObj(): void
    {
        $scalar_val = 42;
        $array_val = ['foo', 'bar'];
        $null_val = null;
        $obj_val = new \stdClass();

        $scalar_key = new ContextKey();
        $array_key = new ContextKey();
        $null_key = new ContextKey();
        $obj_key = new ContextKey();

        $ctx = (new Context())
            ->with($scalar_key, $scalar_val)
            ->with($array_key, $array_val)
            ->with($null_key, $null_val)
            ->with($obj_key, $obj_val);

        $this->assertSame($ctx->get($scalar_key), $scalar_val);
        $this->assertSame($ctx->get($array_key), $array_val);
        $this->assertSame($ctx->get($null_key), $null_val);
        $this->assertSame($ctx->get($obj_key), $obj_val);
    }

    /**
     * @test
     */
    public function storageOrderDoesntMatter(): void
    {
        $arr = [];
        foreach (range(0, 9) as $i) {
            $r = rand(0, 100);
            $key = new ContextKey((string) $r);
            Context::withValue($key, $r);
            $arr[$r] = $key;
        }

        ksort($arr);

        foreach ($arr as $v => $k) {
            $this->assertSame(Context::getValue($k), $v);
        }
    }

    /**
     * @test
     */
    public function staticUseOfCurrentDoesntInterfereWithOtherCalls(): void
    {
        $key1 = new ContextKey();
        $key2 = new ContextKey();
        $key3 = new ContextKey();

        Context::withValue($key1, '111');
        Context::withValue($key2, '222');

        $ctx = Context::withValue($key3, '333', new Context());

        $this->assertSame(Context::getValue($key1), '111');
        $this->assertSame(Context::getValue($key2), '222');

        $this->assertSame(Context::getValue($key3, $ctx), '333');

        $this->assertNull(Context::getValue($key1, $ctx));
        $this->assertNull(Context::getValue($key2, $ctx));
        $this->assertNull(Context::getValue($key3));
    }

    /**
     * @test
     */
    public function reusingKeyOverwritesValue(): void
    {
        $key = new ContextKey();
        $ctx = (new Context())->with($key, 'val1');
        $this->assertSame($ctx->get($key), 'val1');

        $ctx = $ctx->with($key, 'val2');
        $this->assertSame($ctx->get($key), 'val2');
    }

    /**
     * @test
     */
    public function ctxValueNotFoundThrows(): void
    {
        $ctx = (new Context())->with(new ContextKey('foo'), 'bar');
        $this->assertNull($ctx->get(new ContextKey('baz')));
    }

    /**
     * @test
     */
    public function attachAndDetachSetCurrentCtx(): void
    {
        $key = new ContextKey();
        Context::attach((new Context())->with($key, '111'));

        $token = Context::attach((new Context())->with($key, '222'));
        $this->assertSame(Context::getValue($key), '222');

        Context::detach($token);
        $this->assertSame(Context::getValue($key), '111');
    }

    /**
     * @test
     */
    public function instanceSetAndStaticGetUseSameCtx(): void
    {
        $key = new ContextKey('ofoba');
        $val = 'foobar';

        $ctx = (new Context())->with($key, $val);
        Context::attach($ctx);

        $this->assertSame(Context::getValue($key, $ctx), $val);
        $this->assertSame(Context::getValue($key, null), $val);
    }

    /**
     * @test
     */
    public function staticSetAndInstanceGetUseSameCtx(): void
    {
        $key1 = new ContextKey();
        $key2 = new ContextKey();
        $val1 = '111';
        $val2 = '222';

        $ctx = Context::withValue($key1, $val1);
        $ctx = Context::withValue($key2, $val2, $ctx);

        $this->assertSame($ctx->get($key1), $val1);
        $this->assertSame($ctx->get($key2), $val2);
    }

    /**
     * @test
     */
    public function staticWithoutPassedCtxUsesCurrent(): void
    {
        $ctx = Context::withValue(new ContextKey(), '111');
        $first = Context::getCurrent();
        $this->assertSame($first, $ctx);

        $ctx = Context::withValue(new ContextKey(), '222');
        $second = Context::getCurrent();
        $this->assertSame($second, $ctx);

        $this->assertNotSame($first, $second);
    }

    /**
     * @test
     */
    public function staticWithPassedCtxDoesNotUseCurrent(): void
    {
        $key1 = new ContextKey();
        $currentCtx = Context::withValue($key1, '111');

        $key2 = new ContextKey();
        $otherCtx = Context::withValue($key2, '222', new Context());
        $this->assertSame($currentCtx, Context::getCurrent());
    }
}
