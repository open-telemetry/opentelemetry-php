<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Instrumentation\AutoInstrumentation;

use OpenTelemetry\API\Instrumentation\AutoInstrumentation\NoopHookManager;
use OpenTelemetry\Context\ContextInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NoopHookManager::class)]
class NoopHookManagerTest extends TestCase
{
    public function test_enable_disable(): void
    {
        $context = $this->createMock(ContextInterface::class);

        $this->assertSame($context, NoopHookManager::enable($context));
        $this->assertSame($context, NoopHookManager::disable($context));
    }
}
