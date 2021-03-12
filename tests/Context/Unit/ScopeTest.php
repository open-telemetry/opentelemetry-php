<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Context\Unit;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextKey;
use OpenTelemetry\Context\ContextValueNotFoundException;
use OpenTelemetry\Context\Scope;
use PHPUnit\Framework\TestCase;

class ScopeTest extends TestCase
{
    /**
     * @test
     */
    public function scopeCloseRestoresContext()
    {
        $key = new ContextKey();
        $ctx = (new Context())->set($key, 'test');
        $scope = new Scope(Context::attach($ctx));

        $this->assertSame('test', Context::getValue($key));

        $scope->close();

        $this->expectException(ContextValueNotFoundException::class);
        Context::getValue($key);
    }

    /**
     * @test
     */
    public function testNestedScope()
    {
        $key = new ContextKey();
        $ctx1 = (new Context())->set($key, 'test1');
        $scope1 = new Scope(Context::attach($ctx1));
        $this->assertSame('test1', Context::getValue($key));
        
        $ctx2 = (new Context())->set($key, 'test2');
        $scope2 = new Scope(Context::attach($ctx2));
        $this->assertSame('test2', Context::getValue($key));
        
        $scope2->close();
        $this->assertSame('test1', Context::getValue($key));
        
        $scope1->close();
        $this->expectException(ContextValueNotFoundException::class);
        Context::getValue($key);
    }
}
