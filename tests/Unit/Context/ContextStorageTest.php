<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Context;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextStorage;
use OpenTelemetry\Context\ContextStorageHead;
use OpenTelemetry\Context\ContextStorageNode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ContextStorage::class)]
#[CoversClass(ContextStorageHead::class)]
#[CoversClass(ContextStorageNode::class)]
class ContextStorageTest extends TestCase
{
    public function test_scope_returns_null_in_root(): void
    {
        $storage = new ContextStorage();
        $this->assertNull($storage->scope());
    }

    public function test_scope_returns_non_null_after_attach(): void
    {
        $storage = new ContextStorage();
        $storage->attach($storage->current());
        $this->assertNotNull($storage->scope());
    }

    public function test_scope_returns_null_in_new_fork(): void
    {
        $storage = new ContextStorage();
        $storage->attach($storage->current());
        $storage->fork(1);
        $storage->switch(1);
        $this->assertNull($storage->scope());
    }

    public function test_storage_switch_treats_unknown_id_as_main(): void
    {
        $storage = new ContextStorage();

        $storage->fork(1);
        $storage->attach($storage->current());
        $storage->switch(1);

        $storage->switch(2);
        $this->assertNotNull($storage->scope());
    }

    public function test_storage_switch_switches_context(): void
    {
        $storage = new ContextStorage();
        $main = Context::getRoot();
        $fork = Context::getRoot();

        $scopeMain = $storage->attach($main);

        // Fiber start
        $storage->fork(1);
        $storage->switch(1);
        $this->assertSame($main, $storage->current());

        $scopeFork = $storage->attach($fork);
        $this->assertSame($fork, $storage->current());

        // Fiber suspend
        $storage->switch(0);
        $this->assertSame($main, $storage->current());

        // Fiber resume
        $storage->switch(1);
        $this->assertSame($fork, $storage->current());

        $scopeFork->detach();

        // Fiber return
        $storage->switch(0);
        $storage->destroy(1);

        $scopeMain->detach();
    }

    public function test_storage_fork_keeps_forked_root(): void
    {
        $storage = new ContextStorage();
        $main = Context::getRoot();

        $scopeMain = $storage->attach($main);
        $storage->fork(1);
        $scopeMain->detach();

        $storage->switch(1);
        $this->assertSame($main, $storage->current());

        $storage->switch(0);
        $storage->destroy(1);
    }
}
