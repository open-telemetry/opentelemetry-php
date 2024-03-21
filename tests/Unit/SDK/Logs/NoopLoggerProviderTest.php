<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Logs;

use OpenTelemetry\API\Logs\Noop\NoopLogger;
use OpenTelemetry\SDK\Logs\NoopLoggerProvider;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Logs\NoopLoggerProvider
 */
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
