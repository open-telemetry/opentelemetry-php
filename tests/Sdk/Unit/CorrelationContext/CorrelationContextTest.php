<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\CorrelationContext;

use OpenTelemetry\Context\ContextKey;
use OpenTelemetry\Context\ContextValueNotFoundException;
use OpenTelemetry\Sdk\CorrelationContext;
use PHPUnit\Framework\TestCase;

class CorrelationContextTest extends TestCase
{
    /**
     * @test
     */
    public function testRemoveCorrelationFromBeginning()
    {
        $key1 = new ContextKey();
        $key2 = new ContextKey();
        $cctx = (new CorrelationContext())->set($key1, 'foo')->set($key2, 'bar');
        $cctx_res = $cctx->removeCorrelation($key2);
        $this->assertEquals('foo', $cctx_res->get($key1));
        $this->expectException(ContextValueNotFoundException::class);
        $cctx_res->get($key2);
    }

    /**
     * @test
     */
    public function testRemoveCorrelationFromMiddle()
    {
        $key1 = new ContextKey('key1');
        $key2 = new ContextKey('key2');
        $key3 = new ContextKey('key3');
        $cctx = (new CorrelationContext())->set($key1, 'foo')->set($key2, 'bar')->set($key3, 'baz');
        $cctx_res = $cctx->removeCorrelation($key2);
        $this->assertEquals('foo', $cctx_res->get($key1));
        $this->assertEquals('baz', $cctx_res->get($key3));
        $this->expectException(ContextValueNotFoundException::class);
        $cctx_res->get($key2);
    }

    /**
     * @test
     */
    public function testRemoveCorrelationFromEnd()
    {
        $key1 = new ContextKey();
        $key2 = new ContextKey();
        $cctx = (new CorrelationContext())->set($key2, 'bar')->set($key1, 'foo');
        $cctx_res = $cctx->removeCorrelation($key2);
        $this->assertEquals('foo', $cctx_res->get($key1));
        $this->expectException(ContextValueNotFoundException::class);
        $cctx_res->get($key2);
    }

    /**
     * @test
     */
    public function testOnlyItemInTheContext()
    {
        $key1 = new ContextKey();
        $cctx = (new CorrelationContext())->set($key1, 'foo');
        $this->assertEquals('foo', $cctx->get($key1));
    }

    /**
     * @test
     */
    public function testWrongKeyValue()
    {
        $key1 = new ContextKey();
        $key2 = new ContextKey();
        $cctx = (new CorrelationContext())->set($key2, 'bar')->set($key1, 'foo');
        $this->assertNotEquals('bar', $cctx->get($key1));
        $this->assertNotEquals('foo', $cctx->get($key2));
    }
}
