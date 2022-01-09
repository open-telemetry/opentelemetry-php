<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Context\Unit;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextKey;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\Context\Context
 */
class ContextTest extends TestCase
{
    public function test_activate(): void
    {
        $context = new Context();

        $this->assertNotSame($context, Context::getCurrent());
        $context->activate();
        $this->assertSame($context, Context::getCurrent());
    }

    public function test_ctx_can_store_values_by_key(): void
    {
        $key1 = new ContextKey('key1');
        $key2 = new ContextKey('key2');

        $ctx = (new Context())->with($key1, 'val1')->with($key2, 'val2');

        $this->assertSame($ctx->get($key1), 'val1');
        $this->assertSame($ctx->get($key2), 'val2');
    }

    public function test_set_does_not_mutate_the_original(): void
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

    public function test_ctx_key_names_are_not_ids(): void
    {
        $key_name = 'foo';

        $key1 = new ContextKey($key_name);
        $key2 = new ContextKey($key_name);

        $ctx = (new Context())->with($key1, 'val1')->with($key2, 'val2');

        $this->assertSame($ctx->get($key1), 'val1');
        $this->assertSame($ctx->get($key2), 'val2');
    }

    public function test_empty_ctx_keys_are_valid(): void
    {
        $key1 = new ContextKey();
        $key2 = new ContextKey();

        $ctx = (new Context())->with($key1, 'val1')->with($key2, 'val2');

        $this->assertSame($ctx->get($key1), 'val1');
        $this->assertSame($ctx->get($key2), 'val2');
    }

    public function test_ctx_can_store_scalar_array_null_and_obj(): void
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

    public function test_storage_order_doesnt_matter(): void
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

    public function test_static_use_of_current_doesnt_interfere_with_other_calls(): void
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

    public function test_reusing_key_overwrites_value(): void
    {
        $key = new ContextKey();
        $ctx = (new Context())->with($key, 'val1');
        $this->assertSame($ctx->get($key), 'val1');

        $ctx = $ctx->with($key, 'val2');
        $this->assertSame($ctx->get($key), 'val2');
    }

    public function test_ctx_value_not_found_throws(): void
    {
        $ctx = (new Context())->with(new ContextKey('foo'), 'bar');
        $this->assertNull($ctx->get(new ContextKey('baz')));
    }

    public function test_attach_and_detach_set_current_ctx(): void
    {
        $key = new ContextKey();
        Context::attach((new Context())->with($key, '111'));

        $token = Context::attach((new Context())->with($key, '222'));
        $this->assertSame(Context::getValue($key), '222');

        Context::detach($token);
        $this->assertSame(Context::getValue($key), '111');
    }

    public function test_instance_set_and_static_get_use_same_ctx(): void
    {
        $key = new ContextKey('ofoba');
        $val = 'foobar';

        $ctx = (new Context())->with($key, $val);
        Context::attach($ctx);

        $this->assertSame(Context::getValue($key, $ctx), $val);
        $this->assertSame(Context::getValue($key, null), $val);
    }

    public function test_static_set_and_instance_get_use_same_ctx(): void
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

    public function test_static_without_passed_ctx_uses_current(): void
    {
        $ctx = Context::withValue(new ContextKey(), '111');
        $first = Context::getCurrent();
        $this->assertSame($first, $ctx);

        $ctx = Context::withValue(new ContextKey(), '222');
        $second = Context::getCurrent();
        $this->assertSame($second, $ctx);

        $this->assertNotSame($first, $second);
    }

    public function test_static_with_passed_ctx_does_not_use_current(): void
    {
        $key1 = new ContextKey();
        $currentCtx = Context::withValue($key1, '111');

        $key2 = new ContextKey();
        $otherCtx = Context::withValue($key2, '222', new Context());
        $this->assertSame($currentCtx, Context::getCurrent());
    }

    public function test_storage_switch_switches_context(): void
    {
        $main = new Context();
        $fork = new Context();

        $scopeMain = Context::attach($main);

        // Fiber start
        Context::storage()->fork(1);
        Context::storage()->switch(1);
        $this->assertSame($main, Context::getCurrent());

        $scopeFork = Context::attach($fork);
        $this->assertSame($fork, Context::getCurrent());

        // Fiber suspend
        Context::storage()->switch(0);
        $this->assertSame($main, Context::getCurrent());

        // Fiber resume
        Context::storage()->switch(1);
        $this->assertSame($fork, Context::getCurrent());

        $scopeFork->detach();

        // Fiber return
        Context::storage()->switch(0);
        Context::storage()->destroy(1);

        $scopeMain->detach();
    }

    public function test_storage_fork_keeps_forked_root(): void
    {
        $main = new Context();

        $scopeMain = Context::attach($main);
        Context::storage()->fork(1);
        $scopeMain->detach();

        Context::storage()->switch(1);
        $this->assertSame($main, Context::getCurrent());

        Context::storage()->switch(0);
        Context::storage()->destroy(1);
    }
}
