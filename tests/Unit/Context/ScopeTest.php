<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Context;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextKey;
use OpenTelemetry\Context\ContextStorage;
use OpenTelemetry\Context\ScopeInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\Context\ContextStorageNode
 */
class ScopeTest extends TestCase
{
    public function test_scope_close_restores_context(): void
    {
        $key = new ContextKey();
        $ctx = Context::getRoot()->with($key, 'test');
        $scope = $ctx->activate();

        $this->assertSame('test', Context::getCurrent()->get($key));

        $scope->detach();

        $this->assertNull(Context::getCurrent()->get($key));
    }

    public function test_nested_scope(): void
    {
        $key = new ContextKey();
        $ctx1 = Context::getRoot()->with($key, 'test1');
        $scope1 = $ctx1->activate();
        $this->assertSame('test1', Context::getCurrent()->get($key));

        $ctx2 = Context::getRoot()->with($key, 'test2');
        $scope2 = $ctx2->activate();
        $this->assertSame('test2', Context::getCurrent()->get($key));

        $scope2->detach();
        $this->assertSame('test1', Context::getCurrent()->get($key));

        $scope1->detach();
        $this->assertNull(Context::getCurrent()->get($key));
    }

    public function test_detached_scope_detach(): void
    {
        $scope1 = Context::getCurrent()->activate();

        $this->assertSame(0, $scope1->detach());
        /** @phpstan-ignore-next-line */
        $this->assertSame(ScopeInterface::DETACHED, @$scope1->detach() & ScopeInterface::DETACHED);
    }

    public function test_order_mismatch_scope_detach(): void
    {
        $scope1 = Context::getCurrent()->activate();
        $scope2 = Context::getCurrent()->activate();

        $this->assertSame(ScopeInterface::MISMATCH, @$scope1->detach() & ScopeInterface::MISMATCH);
        $this->assertSame(0, $scope2->detach());
    }

    public function test_order_mismatch_scope_detach_depth(): void
    {
        $contextStorage = new ContextStorage();
        $context = $contextStorage->current();

        $scope1 = $contextStorage->attach($context);
        $scope2 = $contextStorage->attach($context);
        $scope3 = $contextStorage->attach($context);
        $scope4 = $contextStorage->attach($context);

        $this->assertSame(ScopeInterface::MISMATCH | 2, $scope2->detach());
        $this->assertSame(ScopeInterface::MISMATCH | 1, $scope3->detach());
        $this->assertSame(0, $scope4->detach());
        $this->assertSame(0, $scope1->detach());
    }

    public function test_inactive_scope_detach(): void
    {
        $scope1 = Context::getCurrent()->activate();

        Context::storage()->fork(1);
        Context::storage()->switch(1);
        $this->assertSame(ScopeInterface::INACTIVE, @$scope1->detach() & ScopeInterface::INACTIVE);

        Context::storage()->switch(0);
        Context::storage()->destroy(1);
    }

    public function test_scope_context_returns_context_of_scope(): void
    {
        $storage = new ContextStorage();

        $ctx1 = $storage->current()->with(new ContextKey(), 1);
        $ctx2 = $storage->current()->with(new ContextKey(), 2);

        $scope1 = $storage->attach($ctx1);
        $this->assertSame($ctx1, $scope1->context());

        $scope2 = $storage->attach($ctx2);
        $this->assertSame($ctx1, $scope1->context());
        $this->assertSame($ctx2, $scope2->context());

        $scope2->detach();
        $this->assertSame($ctx2, $scope2->context());
    }

    public function test_scope_local_storage_is_preserved_between_attach_and_scope(): void
    {
        $storage = new ContextStorage();
        $scope = $storage->attach($storage->current());
        $scope['key'] = 'value';
        $scope = $storage->scope();
        $this->assertNotNull($scope);
        $this->assertArrayHasKey('key', $scope); /** @phpstan-ignore-line */
        $this->assertSame('value', $scope['key']);

        unset($scope['key']);
        $scope = $storage->scope();
        $this->assertNotNull($scope);
        $this->assertArrayNotHasKey('key', $scope);
    }
}
