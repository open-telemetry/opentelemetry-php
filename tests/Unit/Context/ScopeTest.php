<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Context\Unit;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextKey;
use OpenTelemetry\Context\ScopeInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\Context\Context
 * @todo check scope vs context - what should this class be testing?
 */
class ScopeTest extends TestCase
{
    public function test_scope_close_restores_context(): void
    {
        $key = new ContextKey();
        $ctx = (new Context())->with($key, 'test');
        $scope = Context::attach($ctx);

        $this->assertSame('test', Context::getValue($key));

        $scope->detach();

        $this->assertNull(Context::getValue($key));
    }

    public function test_nested_scope(): void
    {
        $key = new ContextKey();
        $ctx1 = (new Context())->with($key, 'test1');
        $scope1 = Context::attach($ctx1);
        $this->assertSame('test1', Context::getValue($key));

        $ctx2 = (new Context())->with($key, 'test2');
        $scope2 = Context::attach($ctx2);
        $this->assertSame('test2', Context::getValue($key));

        $scope2->detach();
        $this->assertSame('test1', Context::getValue($key));

        $scope1->detach();
        $this->assertNull(Context::getValue($key));
    }

    public function test_detached_scope_detach(): void
    {
        $scope1 = Context::attach(Context::getCurrent());

        $this->assertSame(0, $scope1->detach());
        $this->assertSame(ScopeInterface::DETACHED, $scope1->detach() & ScopeInterface::DETACHED); // @phpstan-ignore-line
    }

    public function test_order_mismatch_scope_detach(): void
    {
        $scope1 = Context::attach(Context::getCurrent());
        $scope2 = Context::attach(Context::getCurrent());

        $this->assertSame(ScopeInterface::MISMATCH, $scope1->detach() & ScopeInterface::MISMATCH);
        $this->assertSame(0, $scope2->detach());
    }

    public function test_inactive_scope_detach(): void
    {
        $scope1 = Context::attach(Context::getCurrent());

        Context::storage()->fork(1);
        Context::storage()->switch(1);
        $this->assertSame(ScopeInterface::INACTIVE, $scope1->detach() & ScopeInterface::INACTIVE);

        Context::storage()->switch(0);
        Context::storage()->destroy(1);
    }
}
