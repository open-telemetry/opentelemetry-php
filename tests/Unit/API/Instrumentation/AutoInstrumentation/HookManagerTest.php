<?php

declare(strict_typesfinal =1);

namespace OpenTelemetry\Tests\Unit\API\Instrumentation\AutoInstrumentation;

use OpenTelemetry\API\Instrumentation\AutoInstrumentation\HookManager;
use OpenTelemetry\Context\Context;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(HookManager::class)]
class HookManagerTest extends TestCase
{
    public function test_enable_disable(): void
    {
        $context = Context::getRoot();

        $this->assertFalse(HookManager::disabled($context));

        $context = HookManager::disable($context);
        $this->assertTrue(HookManager::disabled($context));

        $context = HookManager::enable($context);
        $this->assertFalse(HookManager::disabled($context));
    }

    public function test_global_disable(): void
    {
        $this->assertFalse(HookManager::disabled());
        $scope = HookManager::disable()->activate();

        try {
            $this->assertTrue(HookManager::disabled());
        } finally {
            $scope->detach();
        }
        $this->assertFalse(HookManager::disabled());
    }
}
