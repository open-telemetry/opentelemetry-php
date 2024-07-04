<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Instrumentation\AutoInstrumentation;

use OpenTelemetry\API\Instrumentation\AutoInstrumentation\NoopHookManager;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NoopHookManager::class)]
class NoopHookManagerTest extends TestCase
{
    public function test_enable_disable(): void
    {
        $context = Context::getRoot();

        $this->assertNull($context->get(NoopHookManager::contextKey()), 'initially unset');
        $this->assertTrue(NoopHookManager::enable($context)->get(NoopHookManager::contextKey()));
        $this->assertFalse(NoopHookManager::disable($context)->get(NoopHookManager::contextKey()));
    }
}
