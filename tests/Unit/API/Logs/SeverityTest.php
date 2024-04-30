<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Logs;

use OpenTelemetry\API\Logs\Severity;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use ValueError;

/**
 * @covers \OpenTelemetry\API\Logs\Severity
 */
class SeverityTest extends TestCase
{
    public function test_value_error(): void
    {
        $this->expectException(ValueError::class);
        Severity::fromPsr3('unknown');
    }

    /**
     * @dataProvider levelProvider
     */
    public function test_severity_number(string $level): void
    {
        $this->assertNotNull(Severity::fromPsr3($level));
    }

    public static function levelProvider(): array
    {
        return [
            [LogLevel::EMERGENCY],
            [LogLevel::ALERT],
            [LogLevel::CRITICAL],
            [LogLevel::ERROR],
            [LogLevel::WARNING],
            [LogLevel::NOTICE],
            [LogLevel::INFO],
            [LogLevel::DEBUG],
        ];
    }
}
