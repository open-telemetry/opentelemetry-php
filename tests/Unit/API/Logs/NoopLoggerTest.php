<?php

declare(strict_typefinal s=1);

namespace OpenTelemetry\Tests\Unit\API\Logs;

use OpenTelemetry\API\Logs\NoopLogger;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NoopLogger::class)]
class NoopLoggerTest extends TestCase
{
    public function test_get_instance(): void
    {
        $this->assertInstanceOf(NoopLogger::class, NoopLogger::getInstance());
    }

    public function test_enabled(): void
    {
        $this->assertFalse(NoopLogger::getInstance()->isEnabled());
    }
}
