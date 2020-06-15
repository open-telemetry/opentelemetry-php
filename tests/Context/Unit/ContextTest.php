<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Context\Unit;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextKey;
use OpenTelemetry\Context\ContextValueNotFoundException;
use PHPUnit\Framework\TestCase;

class ContextTest extends TestCase
{
    /**
     * @test
     */
    public function ctxCanStoreValuesByKey()
    {
        $key1 = new ContextKey('key1');
        $key2 = new ContextKey('key2');

        $ctx = (new Context())->set($key1, 'val1')->set($key2, 'val2');

        $this->assertSame($ctx->get($key1), 'val1');
        $this->assertSame($ctx->get($key2), 'val2');
    }

    /**
     * @test
     */
    public function setDoesNotMutateTheOriginal()
    {
        $key1 = new ContextKey();
        $key2 = new ContextKey();

        $parent = (new Context())->set($key1, 'foo');
        $child = $parent->set($key2, 'bar');

        $this->assertSame($child->get($key1), 'foo');
        $this->assertSame($child->get($key2), 'bar');
        $this->assertSame($parent->get($key1), 'foo');

        $this->expectException(ContextValueNotFoundException::class);
        $parent->get($key2);
    }

    /**
     * @test
     */
    public function ctxKeyNamesAreNotIds()
    {
        $key_name = 'foo';

        $key1 = new ContextKey($key_name);
        $key2 = new ContextKey($key_name);

        $ctx = (new Context())->set($key1, 'val1')->set($key2, 'val2');

        $this->assertSame($ctx->get($key1), 'val1');
        $this->assertSame($ctx->get($key2), 'val2');
    }

    /**
     * @test
     */
    public function emptyCtxKeysAreValid()
    {
        $key1 = new ContextKey();
        $key2 = new ContextKey();

        $ctx = (new Context())->set($key1, 'val1')->set($key2, 'val2');

        $this->assertSame($ctx->get($key1), 'val1');
        $this->assertSame($ctx->get($key2), 'val2');
    }

    /**
     * @test
     */
    public function ctxCanStoreScalarArrayNullAndObj()
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
            ->set($scalar_key, $scalar_val)
            ->set($array_key, $array_val)
            ->set($null_key, $null_val)
            ->set($obj_key, $obj_val);

        $this->assertSame($ctx->get($scalar_key), $scalar_val);
        $this->assertSame($ctx->get($array_key), $array_val);
        $this->assertSame($ctx->get($null_key), $null_val);
        $this->assertSame($ctx->get($obj_key), $obj_val);
    }

    /**
     * @test
     */
    public function storageOrderDoesntMatter()
    {
        $arr = [];
        foreach (range(0, 9) as $i) {
            $r = rand(0, 100);
            $key = new ContextKey((string) $r);
            Context::setValue($key, $r);
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
    public function staticUseOfCurrentDoesntInterfereWithOtherCalls()
    {
        $key1 = new ContextKey();
        $key2 = new ContextKey();
        $key3 = new ContextKey();

        Context::setValue($key1, '111');
        Context::setValue($key2, '222');

        $ctx = Context::setValue($key3, '333', new Context());

        $this->assertSame(Context::getValue($key1), '111');
        $this->assertSame(Context::getValue($key2), '222');

        $this->assertSame(Context::getValue($key3, $ctx), '333');

        $e = null;

        try {
            Context::getValue($key1, $ctx);
        } catch (\Exception $e) {
        }
        $this->assertInstanceOf(ContextValueNotFoundException::class, $e);

        $e = null;

        try {
            Context::getValue($key2, $ctx);
        } catch (\Exception $e) {
        }
        $this->assertInstanceOf(ContextValueNotFoundException::class, $e);

        $e = null;

        try {
            Context::getValue($key3);
        } catch (\Exception $e) {
        }
        $this->assertInstanceOf(ContextValueNotFoundException::class, $e);
    }

    /**
     * @test
     */
    public function reusingKeyOverwritesValue()
    {
        $key = new ContextKey();
        $ctx = (new Context())->set($key, 'val1');
        $this->assertSame($ctx->get($key), 'val1');

        $ctx = $ctx->set($key, 'val2');
        $this->assertSame($ctx->get($key), 'val2');
    }

    /**
     * @test
     */
    public function ctxValueNotFoundThrows()
    {
        $this->expectException(ContextValueNotFoundException::class);
        $ctx = (new Context())->set(new ContextKey('foo'), 'bar');
        $ctx->get(new ContextKey('baz'));
    }

    /**
     * @test
     */
    public function attachAndDetachSetCurrentCtx()
    {
        $key = new ContextKey();
        Context::attach((new Context())->set($key, '111'));

        $token = Context::attach((new Context())->set($key, '222'));
        $this->assertSame(Context::getValue($key), '222');

        Context::detach($token);
        $this->assertSame(Context::getValue($key), '111');
    }

    /**
     * @test
     */
    public function instanceSetAndStaticGetUseSameCtx()
    {
        $key = new ContextKey();
        $val = 'foobar';

        $ctx = (new Context())->set($key, $val);
        Context::attach($ctx);

        $this->assertSame(Context::getValue($key, $ctx), $val);
        $this->assertSame(Context::getValue($key), $val);
    }

    /**
     * @test
     */
    public function staticSetAndInstanceGetUseSameCtx()
    {
        $key1 = new ContextKey();
        $key2 = new ContextKey();
        $val1 = '111';
        $val2 = '222';

        $ctx = Context::setValue($key1, $val1);
        $ctx = Context::setValue($key2, $val2, $ctx);

        $this->assertSame($ctx->get($key1), $val1);
        $this->assertSame($ctx->get($key2), $val2);
    }

    /**
     * @test
     */
    public function staticWithoutPassedCtxUsesCurrent()
    {
        $ctx = Context::setValue(new ContextKey(), '111');
        $first = Context::getCurrent();
        $this->assertSame($first, $ctx);

        $ctx = Context::setValue(new ContextKey(), '222');
        $second = Context::getCurrent();
        $this->assertSame($second, $ctx);

        $this->assertNotSame($first, $second);
    }

    /**
     * @test
     */
    public function staticWithPassedCtxDoesNotUseCurrent()
    {
        $key1 = new ContextKey();
        $currentCtx = Context::setValue($key1, '111');

        $key2 = new ContextKey();
        $otherCtx = Context::setValue($key2, '222', new Context());
        $this->assertSame($currentCtx, Context::getCurrent());
    }
}
