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
        /** @var CorrelationContext $cctx */
        $cctx = (new CorrelationContext())->set($key1, 'foo')->set($key2, 'bar');
        /** @var CorrelationContext $cctx_res */
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
        /** @var CorrelationContext $cctx */
        $cctx = (new CorrelationContext())->set($key1, 'foo')->set($key2, 'bar')->set($key3, 'baz');
        /** @var CorrelationContext $cctx_res */
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
        /** @var CorrelationContext $cctx */
        $cctx = (new CorrelationContext())->set($key2, 'bar')->set($key1, 'foo');
        /** @var CorrelationContext $cctx_res */
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

    /**
     * @test
     */
    public function testClearCorrelations()
    {
        $key1 = new ContextKey();
        $key2 = new ContextKey();

        /** @var CorrelationContext $cctx */
        $cctx = (new CorrelationContext())->set($key1, 'foo')->set($key2, 'bar');

        $this->assertEquals('foo', $cctx->get($key1));
        $this->assertEquals('bar', $cctx->get($key2));

        $cctx->clearCorrelations();
        $this->expectException(ContextValueNotFoundException::class);
        $cctx->get($key1);
    }

    /**
     * @test
     */
    public function testGetCorrelations()
    {
        $key1 = new ContextKey();
        $key2 = new ContextKey();

        /** @var CorrelationContext $cctx */
        $cctx = (new CorrelationContext())->set($key1, 'foo')->set($key2, 'bar');

        $res = [];
        foreach ($cctx->getCorrelations() as $k => $v) {
            // I am inverting the k/v pairs in an array here because php does not allow for object keys
            $res[$v] = $k;
        }

        $this->assertSame($key1, $res['foo']);
        $this->assertSame($key2, $res['bar']);
    }
}
