<?php

declare(strict_types=1);

namespace OpenTelemetry\Example\Unit\API\Common\Log;

use OpenTelemetry\API\Logs\NoopLogger;
use OpenTelemetry\API\Logs\NoopLoggerProvider;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\API\Logs\NoopLogger
 */
class NoopLoggerProviderTest extends TestCase
{
    public function test_provides_logger(): void
    {
        $logger = (new NoopLoggerProvider())->getLogger('foo');
        $this->assertInstanceOf(NoopLogger::class, $logger);
    }
}
