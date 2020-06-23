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

        $ctx = (new MyClassUsesContextTrait())->set($key1, 'val1')->set($key2, 'val2');

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

        $parent = (new MyClassUsesContextTrait())->set($key1, 'foo');
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

        $ctx = (new MyClassUsesContextTrait())->set($key1, 'val1')->set($key2, 'val2');

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

        $ctx = (new MyClassUsesContextTrait())->set($key1, 'val1')->set($key2, 'val2');

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

        $ctx = (new MyClassUsesContextTrait())
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
            MyClassUsesContextTrait::setValue($key, $r);
            $arr[$r] = $key;
        }

        ksort($arr);

        foreach ($arr as $v => $k) {
            $this->assertSame(MyClassUsesContextTrait::getValue($k), $v);
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

        MyClassUsesContextTrait::setValue($key1, '111');
        MyClassUsesContextTrait::setValue($key2, '222');

        $ctx = MyClassUsesContextTrait::setValue($key3, '333', new MyClassUsesContextTrait());

        $this->assertSame(MyClassUsesContextTrait::getValue($key1), '111');
        $this->assertSame(MyClassUsesContextTrait::getValue($key2), '222');

        $this->assertSame(MyClassUsesContextTrait::getValue($key3, $ctx), '333');

        $e = null;

        try {
            MyClassUsesContextTrait::getValue($key1, $ctx);
        } catch (\Exception $e) {
        }
        $this->assertInstanceOf(ContextValueNotFoundException::class, $e);

        $e = null;

        try {
            MyClassUsesContextTrait::getValue($key2, $ctx);
        } catch (\Exception $e) {
        }
        $this->assertInstanceOf(ContextValueNotFoundException::class, $e);

        $e = null;

        try {
            MyClassUsesContextTrait::getValue($key3);
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
        $ctx = (new MyClassUsesContextTrait())->set($key, 'val1');
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
        $ctx = (new MyClassUsesContextTrait())->set(new ContextKey('foo'), 'bar');
        $ctx->get(new ContextKey('baz'));
    }

    /**
     * @test
     */
    public function attachAndDetachSetCurrentCtx()
    {
        $key = new ContextKey();
        Context::attach((new MyClassUsesContextTrait())->set($key, '111'));

        $token = Context::attach((new MyClassUsesContextTrait())->set($key, '222'));
        $this->assertSame(Context::getValue($key), '222');

        Context::detach($token);
        $this->assertSame(Context::getValue($key), '111');
    }

    /**
     * @test
     */
    public function instanceSetAndStaticGetUseSameCtx()
    {
        $key = new ContextKey('ofoba');
        $val = 'foobar';

        $ctx = (new MyClassUsesContextTrait())->set($key, $val);
        MyClassUsesContextTrait::attach($ctx);

        $this->assertSame(MyClassUsesContextTrait::getValue($key, $ctx), $val);
        $this->assertSame(MyClassUsesContextTrait::getValue($key, null, true), $val);
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

        $ctx = MyClassUsesContextTrait::setValue($key1, $val1);
        $ctx = MyClassUsesContextTrait::setValue($key2, $val2, $ctx);

        $this->assertSame($ctx->get($key1), $val1);
        $this->assertSame($ctx->get($key2), $val2);
    }

    /**
     * @test
     */
    public function staticWithoutPassedCtxUsesCurrent()
    {
        $ctx = MyClassUsesContextTrait::setValue(new ContextKey(), '111');
        $first = MyClassUsesContextTrait::getCurrent();
        $this->assertSame($first, $ctx);

        $ctx = MyClassUsesContextTrait::setValue(new ContextKey(), '222');
        $second = MyClassUsesContextTrait::getCurrent();
        $this->assertSame($second, $ctx);

        $this->assertNotSame($first, $second);
    }

    /**
     * @test
     */
    public function staticWithPassedCtxDoesNotUseCurrent()
    {
        $key1 = new ContextKey();
        $currentCtx = MyClassUsesContextTrait::setValue($key1, '111');

        $key2 = new ContextKey();
        $otherCtx = MyClassUsesContextTrait::setValue($key2, '222', new MyClassUsesContextTrait());
        $this->assertSame($currentCtx, MyClassUsesContextTrait::getCurrent());
    }
}

class MyClassUsesContextTrait
{
    use Context;
}
