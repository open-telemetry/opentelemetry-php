<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\CorrelationContext;


use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextKey;
use OpenTelemetry\sdk\CorrelationContext\CorrelationContext;
use PHPUnit\Framework\TestCase;

class CorrelationContextTest extends TestCase
{
    /**
     * @test
     */
    public function getCorrelationsTest()
    {
        //todo
    }

    /**
     * @test
     */
    public function getTest()
    {
        $key1 = new ContextKey('key1');
        $key2 = new ContextKey('key2');
        $ctx = (new CorrelationContext())->set($key1, 'val1')->set($key2, 'val2');
        $this->assertSame($ctx->get($key1), 'val1');
        $this->assertSame($ctx->get($key2), 'val2');
    }

    /**
     * @test
     */
    public function correlationRemovalTest()
    {
        $key1 = new ContextKey('key1');
        $key2 = new ContextKey('key2');
        $key3 = new ContextKey('key3');
        $key4 = new ContextKey('key4');
        $ctx = (new Context())->set($key1, 'val1')->set($key2, 'val2')->set($key3, 'val3')->set($key4, 'val4');
        $result = $ctx->removeCorrelation($key3);
        print_r($result);
    }
}
