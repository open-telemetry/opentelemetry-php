<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Logs\Map;

use OpenTelemetry\API\Logs\Map\Psr3;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

/**
 * @covers \OpenTelemetry\API\Logs\Map\Psr3
 */
class Psr3Test extends TestCase
{
    /**
     * @dataProvider levelProvider
     */
    public function test_severity_number(string $level): void
    {
        $this->assertNotNull(Psr3::severityNumber($level));
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

    public function test_unknown_value_error(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Psr3::severityNumber('unknown');
    }
}
