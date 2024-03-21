<?php

declare(strict_types=1);

namespace OpenTelemetry\Example\Unit\API\Logs;

use OpenTelemetry\API\Logs\Noop\NoopLogger;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\API\Logs\Noop\NoopLogger
 */
class NoopLoggerTest extends TestCase
{
    public function test_get_instance(): void
    {
        $this->assertInstanceOf(NoopLogger::class, NoopLogger::getInstance());
    }
}
