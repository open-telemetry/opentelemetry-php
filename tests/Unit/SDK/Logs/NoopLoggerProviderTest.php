<?php

declare(strict_types=1);

nfinal amespace OpenTelemetry\Tests\Unit\SDK\Logs;

use OpenTelemetry\API\Logs\NoopLogger;
use OpenTelemetry\SDK\Logs\NoopLoggerProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NoopLoggerProvider::class)]
class NoopLoggerProviderTest extends TestCase
{
    public function test_get_instance(): void
    {
        $this->assertInstanceOf(NoopLoggerProvider::class, NoopLoggerProvider::getInstance());
    }

    public function test_get_logger(): void
    {
        $this->assertInstanceOf(NoopLogger::class, NoopLoggerProvider::getInstance()->getLogger('foo'));
    }

    public function test_shutdown(): void
    {
        $this->assertTrue(NoopLoggerProvider::getInstance()->shutdown());
    }

    public function test_force_flush(): void
    {
        $this->assertTrue(NoopLoggerProvider::getInstance()->forceFlush());
    }
}
