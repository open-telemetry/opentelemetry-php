<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Baggage;

use OpenTelemetry\Context\ContextKey;
use OpenTelemetry\Sdk\Baggage;
use PHPUnit\Framework\TestCase;

class BaggageTest extends TestCase
{
    public function testRemoveCorrelationFromBeginning(): void
    {
        $key1 = new ContextKey();
        $key2 = new ContextKey();
        /** @var Baggage $cctx */
        $cctx = (new Baggage())->set($key1, 'foo')->set($key2, 'bar');
        /** @var Baggage $cctx_res */
        $cctx_res = $cctx->removeCorrelation($key2);
        $this->assertEquals('foo', $cctx_res->get($key1));

        $this->assertNull($cctx_res->get($key2));
    }

    public function testRemoveCorrelationFromMiddle(): void
    {
        $key1 = new ContextKey('key1');
        $key2 = new ContextKey('key2');
        $key3 = new ContextKey('key3');
        /** @var Baggage $cctx */
        $cctx = (new Baggage())->set($key1, 'foo')->set($key2, 'bar')->set($key3, 'baz');
        /** @var Baggage $cctx_res */
        $cctx_res = $cctx->removeCorrelation($key2);
        $this->assertEquals('foo', $cctx_res->get($key1));
        $this->assertEquals('baz', $cctx_res->get($key3));

        $this->assertNull($cctx_res->get($key2));
    }

    public function testRemoveCorrelationFromEnd(): void
    {
        $key1 = new ContextKey();
        $key2 = new ContextKey();
        /** @var Baggage $cctx */
        $cctx = (new Baggage())->set($key2, 'bar')->set($key1, 'foo');
        /** @var Baggage $cctx_res */
        $cctx_res = $cctx->removeCorrelation($key2);
        $this->assertEquals('foo', $cctx_res->get($key1));

        $this->assertNull($cctx_res->get($key2));
    }

    public function testOnlyItemInTheContext(): void
    {
        $key1 = new ContextKey();
        $cctx = (new Baggage())->set($key1, 'foo');
        $this->assertEquals('foo', $cctx->get($key1));
    }

    public function testWrongKeyValue(): void
    {
        $key1 = new ContextKey();
        $key2 = new ContextKey();
        $cctx = (new Baggage())->set($key2, 'bar')->set($key1, 'foo');
        $this->assertNotEquals('bar', $cctx->get($key1));
        $this->assertNotEquals('foo', $cctx->get($key2));
    }

    public function testClearCorrelations(): void
    {
        $key1 = new ContextKey();
        $key2 = new ContextKey();

        /** @var Baggage $cctx */
        $cctx = (new Baggage())->set($key1, 'foo')->set($key2, 'bar');

        $this->assertEquals('foo', $cctx->get($key1));
        $this->assertEquals('bar', $cctx->get($key2));

        $cctx->clearCorrelations();
        $this->assertNull($cctx->get($key1));
    }

    public function testGetCorrelations(): void
    {
        $key1 = new ContextKey();
        $key2 = new ContextKey();

        /** @var Baggage $cctx */
        $cctx = (new Baggage())->set($key1, 'foo')->set($key2, 'bar');

        $res = [];
        foreach ($cctx->getCorrelations() as $k => $v) {
            // I am inverting the k/v pairs in an array here because php does not allow for object keys
            $res[$v] = $k;
        }

        $this->assertSame($key1, $res['foo']);
        $this->assertSame($key2, $res['bar']);
    }
}
