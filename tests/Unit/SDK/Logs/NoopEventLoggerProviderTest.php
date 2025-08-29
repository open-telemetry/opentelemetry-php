<?php

declare(strict_types=1);

namespace Unit\SDK\Logs;

use OpenTelemetry\SDK\Logs\NoopEventLoggerProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NoopEventLoggerProvider::class)]
class NoopEventLoggerProviderTest extends TestCase
{
    public function test_get_instance(): void
    {
        $this->assertInstanceOf(NoopEventLoggerProvider::class, NoopEventLoggerProvider::getInstance());
    }

    public function test_force_flush(): void
    {
        $this->assertTrue(NoopEventLoggerProvider::getInstance()->forceFlush());
    }
}
