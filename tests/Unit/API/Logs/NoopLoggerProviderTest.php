<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Logs;

use OpenTelemetry\API\Logs\NoopLogger;
use OpenTelemetry\API\Logs\NoopLoggerProvider;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\API\Logs\NoopLoggerProvider
 */
class NoopLoggerProviderTest extends TestCase
{
    public function test_provides_logger(): void
    {
        $logger = (new NoopLoggerProvider())->getLogger('foo');
        $this->assertInstanceOf(NoopLogger::class, $logger);
    }

    public function test_get_instance(): void
    {
        $this->assertInstanceOf(NoopLoggerProvider::class, NoopLoggerProvider::getInstance());
    }
}
