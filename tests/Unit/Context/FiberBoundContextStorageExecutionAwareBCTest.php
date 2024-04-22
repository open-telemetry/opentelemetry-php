<?php

declare(strict_types=1);

namespace OpenTelemetry\Context;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FiberBoundContextStorageExecutionAwareBC::class)]
final class FiberBoundContextStorageExecutionAwareBCTest extends TestCase
{
    public function test_retains_scope_after_bc_switch(): void
    {
        $storage = new FiberBoundContextStorageExecutionAwareBC();

        $storage->attach($storage->current());

        $scope = $storage->scope();

        $storage->fork(1);

        $this->assertSame($scope, $storage->scope());
    }

    public function test_inactive_scope_detach(): void
    {
        $storage = new FiberBoundContextStorageExecutionAwareBC();
        $scope1 = $storage->attach($storage->current());

        $storage->fork(1);
        $storage->switch(1);
        $this->assertSame(ScopeInterface::INACTIVE, @$scope1->detach() & ScopeInterface::INACTIVE);
    }

    public function test_storage_switch_switches_context(): void
    {
        $storage = new FiberBoundContextStorageExecutionAwareBC();
        $main = $storage->current();
        $fork = $storage->current()->with(Context::createKey('-'), 42);

        $scopeMain = $storage->attach($main);

        // Coroutine start
        $storage->fork(1);
        $storage->switch(1);
        $this->assertSame($main, $storage->current());

        $scopeFork = $storage->attach($fork);
        $this->assertSame($fork, $storage->current());

        // Coroutine suspend
        $storage->switch(0);
        $this->assertSame($main, $storage->current());

        // Coroutine resume
        $storage->switch(1);
        $this->assertSame($fork, $storage->current());

        $scopeFork->detach();

        // Coroutine return
        $storage->switch(0);
        $storage->destroy(1);

        $scopeMain->detach();
    }
}
