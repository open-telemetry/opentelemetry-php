<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Context;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextKeys;
use OpenTelemetry\Context\DebugScope;
use OpenTelemetry\Context\ImplicitContextKeyedInterface;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \OpenTelemetry\Context\Context
 */
class ContextTest extends TestCase
{
    public function test_activate(): void
    {
        $context = Context::getRoot();

        $scope = $context->activate();

        try {
            $this->assertSame($context, Context::getCurrent());
        } finally {
            $scope->detach();
        }
    }

    public function test_ctx_can_store_values_by_key(): void
    {
        $key1 = Context::createKey('key1');
        $key2 = Context::createKey('key2');

        $ctx = Context::getRoot()->with($key1, 'val1')->with($key2, 'val2');

        $this->assertSame($ctx->get($key1), 'val1');
        $this->assertSame($ctx->get($key2), 'val2');
    }

    public function test_set_does_not_mutate_the_original(): void
    {
        $key1 = Context::createKey('-');
        $key2 = Context::createKey('-');

        $parent = Context::getRoot()->with($key1, 'foo');
        $child = $parent->with($key2, 'bar');

        $this->assertSame($child->get($key1), 'foo');
        $this->assertSame($child->get($key2), 'bar');
        $this->assertSame($parent->get($key1), 'foo');

        $this->assertNull($parent->get($key2));
    }

    public function test_ctx_key_names_are_not_ids(): void
    {
        $key_name = 'foo';

        $key1 = Context::createKey($key_name);
        $key2 = Context::createKey($key_name);

        $ctx = Context::getRoot()->with($key1, 'val1')->with($key2, 'val2');

        $this->assertSame($ctx->get($key1), 'val1');
        $this->assertSame($ctx->get($key2), 'val2');
    }

    public function test_empty_ctx_keys_are_valid(): void
    {
        $key1 = Context::createKey('-');
        $key2 = Context::createKey('-');

        $ctx = Context::getRoot()->with($key1, 'val1')->with($key2, 'val2');

        $this->assertSame($ctx->get($key1), 'val1');
        $this->assertSame($ctx->get($key2), 'val2');
    }

    public function test_ctx_can_store_scalar_array_null_and_obj(): void
    {
        $scalar_val = 42;
        $array_val = ['foo', 'bar'];
        $null_val = null;
        $obj_val = new \stdClass();

        $scalar_key = Context::createKey('-');
        $array_key = Context::createKey('-');
        $null_key = Context::createKey('-');
        $obj_key = Context::createKey('-');

        $ctx = Context::getRoot()
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
        $context = Context::getRoot();
        $arr = [];
        foreach (range(0, 9) as $i) {
            $r = rand(0, 100);
            $key = Context::createKey((string) $r);
            $context = $context->with($key, $r);
            $arr[$r] = $key;
        }

        ksort($arr);

        foreach ($arr as $v => $k) {
            $this->assertSame($context->get($k), $v);
        }
    }

    public function test_reusing_key_overwrites_value(): void
    {
        $key = Context::createKey('-');
        $ctx = Context::getRoot()->with($key, 'val1');
        $this->assertSame($ctx->get($key), 'val1');

        $ctx = $ctx->with($key, 'val2');
        $this->assertSame($ctx->get($key), 'val2');
    }

    public function test_ctx_value_not_found_returns_null(): void
    {
        $ctx = Context::getRoot()->with(Context::createKey('foo'), 'bar');
        $this->assertNull($ctx->get(Context::createKey('baz')));
    }

    public function test_ctx_value_overwrite_null_returns_null(): void
    {
        $key = Context::createKey('-');
        $ctx = Context::getRoot()->with($key, 'val')->with($key, null);

        $this->assertNull($ctx->get($key));
    }

    public function test_set_get_span_key(): void
    {
        $key = ContextKeys::span();
        $span = new stdClass();

        $ctx = Context::getRoot()->with($key, $span);
        $this->assertSame($span, $ctx->get($key));
    }

    public function test_with_context_value_calls_store_in_context(): void
    {
        $ctx = Context::getRoot();
        $value = $this->createMock(ImplicitContextKeyedInterface::class);
        $value->expects($this->once())->method('storeInContext')->with($ctx)->willReturn($ctx);

        $ctx->withContextValue($value);
    }

    public function test_attach_and_detach_set_current_ctx(): void
    {
        $key = Context::createKey('-');
        $scope = Context::getRoot()->with($key, '111')->activate();

        try {
            $token = Context::getRoot()->with($key, '222')->activate();
            $this->assertSame(Context::getCurrent()->get($key), '222');

            $token->detach();
            $this->assertSame(Context::getCurrent()->get($key), '111');
        } finally {
            $scope->detach();
        }
    }

    public function test_debug_scopes_disabled_env_var(): void
    {
        \putenv('OTEL_PHP_DEBUG_SCOPES_DISABLED=1');

        $context = Context::getRoot();

        $scope = $context->activate();

        try {
            $this->assertNotInstanceOf(DebugScope::class, $scope);
        } finally {
            $scope->detach();
            \putenv('OTEL_PHP_DEBUG_SCOPES_DISABLED');
        }
    }
}
